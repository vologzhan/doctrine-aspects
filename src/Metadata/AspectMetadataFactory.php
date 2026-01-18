<?php

declare(strict_types=1);

namespace Vologzhan\DoctrineAspects\Metadata;

use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use Vologzhan\DoctrineAspects\Exception\AspectException;
use Vologzhan\DoctrineAspects\Metadata\Dto\AspectMetadata;
use Vologzhan\DoctrineAspects\Metadata\Dto\Property;
use Vologzhan\DoctrineAspects\Metadata\Dto\PropertyRel;

class AspectMetadataFactory
{
    /**
     * @throws AspectException
     */
    public static function parse(string $aspectClassName): AspectMetadata
    {
        $aspectReflection = self::getAspectReflection($aspectClassName);

        return self::parseRecursive($aspectReflection);
    }

    /**
     * @throws AspectException
     */
    private static function parseRecursive(\ReflectionClass $class): AspectMetadata
    {
        /** @var Context|null $classContext */
        $classContext = null;

        $properties = [];
        foreach ($class->getProperties() as $prop) {
            $type = $prop->getType();
            if ($type === null) {
                throw new AspectException("'$class->name::$prop->name' must be typed");
            }

            if ($type->getName() === 'array') {
                $docComment = $prop->getDocComment();
                if (!$docComment) {
                    throw new AspectException("'$class->name::$prop->name' array must be typed using '@var ClassName[]'");
                }

                $match = [];
                $isMatched = preg_match('/@var\s+(\S+)\[/', $docComment, $match);
                if (!$isMatched) {
                    throw new AspectException("'$class->name::$prop->name' array must be typed using '@var ClassName[]'");
                }
                $nextClassName = $match[1];

                if ($classContext === null) {
                    $classContext = (new ContextFactory())->createFromReflector($class);
                }
                $resolvedType = (new TypeResolver())->resolve($nextClassName, $classContext);

                $nextClassFullName = (string)$resolvedType->getFqsen();
                $nextAspect = self::getAspectReflection($nextClassFullName);

                $properties[] = new PropertyRel($prop->name, true, self::parseRecursive($nextAspect));
                continue;
            }

            if ($type->isBuiltin() || is_subclass_of($type->getName(), \DateTimeInterface::class)) {
                $properties[] = new Property($prop->name);
                continue;
            }

            $nextAspect = self::getAspectReflection($type->getName());
            $properties[] = new PropertyRel($prop->name, false, self::parseRecursive($nextAspect));
        }

        return new AspectMetadata($class->name, $properties);
    }

    /**
     * @throws AspectException
     */
    private static function getAspectReflection(string $aspectClassName): \ReflectionClass
    {
        try {
            return new \ReflectionClass($aspectClassName);
        } catch (\ReflectionException $e) {
            throw new AspectException("'$aspectClassName' does not exist", 0, $e);
        }
    }
}
