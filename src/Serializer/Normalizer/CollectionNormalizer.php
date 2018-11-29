<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Collections\Collection;

class CollectionNormalizer implements NormalizerInterface
{
    /**
     * @param ArrayCollection $object
     * @param mixed           $format
     * @param array           $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $result = [];

        foreach ($object as $item) {
            if (method_exists($item, 'getId')) {
                $result[] = $item->getId();
            }
        }

        return $result;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Collection;
    }
}
