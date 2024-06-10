<?php

namespace Ehyiah\MappingBundle;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('ehyiah.mapping_bundle.mapping_service')]
interface MappingServiceInterface
{
    public function mapToTarget(object $mappingAwareSourceObject, ?object $targetObject = null, bool $persist = false, bool $flush = false): object;

    public function mapFromTarget(object $sourceObject, object $mappingAwareTargetObject): object;
}
