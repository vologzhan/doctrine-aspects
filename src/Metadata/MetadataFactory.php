<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineDto\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use Vologzhan\DoctrineDto\Exception\DoctrineDtoException;
use Vologzhan\DoctrineDto\Metadata\Dto\DtoMetadata;
use Vologzhan\DoctrineDto\Metadata\Dto\Property;
use Vologzhan\DoctrineDto\Metadata\Dto\PropertyRel;

final class MetadataFactory
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws DoctrineDtoException
     */
    public function create(string $dtoClassName, string $entityClassName): DtoMetadata
    {
        $dtoReflection = self::getDtoReflection($dtoClassName);
        $dtoMetadata = self::createRecursive($dtoReflection);

        $entityMetadata = $this->em->getClassMetadata($entityClassName);
        $this->addEntityMetadataRecursive($dtoMetadata, $entityMetadata);

        return $dtoMetadata;
    }

    /**
     * @throws DoctrineDtoException
     */
    private static function createRecursive(\ReflectionClass $class): DtoMetadata
    {
        /** @var Context|null $classContext */
        $classContext = null;

        $properties = [];
        foreach ($class->getProperties() as $prop) {
            $type = $prop->getType();
            if ($type === null) {
                throw new DoctrineDtoException("'$class->name::$prop->name' must be typed");
            }

            if ($type->getName() === 'array') {
                $docComment = $prop->getDocComment();
                if (!$docComment) {
                    throw new DoctrineDtoException("'$class->name::$prop->name' array must be typed using '@var ClassName[]'");
                }

                $match = [];
                $isMatched = preg_match('/@var\s+(\S+)\[/', $docComment, $match);
                if (!$isMatched) {
                    throw new DoctrineDtoException("'$class->name::$prop->name' array must be typed using '@var ClassName[]'");
                }
                $nextClassName = $match[1];

                if ($classContext === null) {
                    $classContext = (new ContextFactory())->createFromReflector($class);
                }
                $resolvedType = (new TypeResolver())->resolve($nextClassName, $classContext);

                $nextClassFullName = (string)$resolvedType->getFqsen();
                $nextDto = self::getDtoReflection($nextClassFullName);

                $properties[] = new PropertyRel($prop->name, true, self::createRecursive($nextDto));
                continue;
            }

            if ($type->isBuiltin() || is_subclass_of($type->getName(), \DateTimeInterface::class)) {
                $properties[] = new Property($prop->name);
                continue;
            }

            $nextDto = self::getDtoReflection($type->getName());
            $properties[] = new PropertyRel($prop->name, false, self::createRecursive($nextDto));
        }

        return new DtoMetadata($class->name, $properties);
    }

    public function addEntityMetadataRecursive(DtoMetadata $dtoMetadata, ClassMetadata $entityMetadata): void
    {
        $dtoMetadata->tableName = $entityMetadata->getTableName();

        foreach ($dtoMetadata->properties as $prop) {
            if ($prop instanceof PropertyRel) {
                $nextEntity = $entityMetadata->getAssociationMapping($prop->property->name);
                $nextEntityMetadata = $this->em->getClassMetadata($nextEntity['targetEntity']);

                if ($nextEntity['isOwningSide']) {
                    $prop->property->columnName = $nextEntity['joinColumns'][0]['name'];
                    $prop->foreignColumn = $nextEntity['joinColumns'][0]['referencedColumnName'];
                } else {
                    $ownerMapping = $nextEntityMetadata->getAssociationMapping($nextEntity['mappedBy']);
                    $prop->property->columnName = $ownerMapping['joinColumns'][0]['referencedColumnName'];
                    $prop->foreignColumn = $ownerMapping['joinColumns'][0]['name'];
                }

                $this->addEntityMetadataRecursive($prop->dtoMetadata, $nextEntityMetadata);
                continue;
            }

            if (!$entityMetadata->hasField($prop->name)) {
                throw new DoctrineDtoException("'$entityMetadata->name::$prop->name' does not exist");
            }
            $prop->columnName = $entityMetadata->getColumnName($prop->name);
        }
    }

    /**
     * @throws DoctrineDtoException
     */
    private static function getDtoReflection(string $dtoClassName): \ReflectionClass
    {
        try {
            return new \ReflectionClass($dtoClassName);
        } catch (\ReflectionException $e) {
            throw new DoctrineDtoException("'$dtoClassName' does not exist", 0, $e);
        }
    }
}
