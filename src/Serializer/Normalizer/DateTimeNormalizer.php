<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * @param \DateTime $object
     * @param mixed     $format
     * @param array     $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->format(\DateTIme::RFC3339);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof \DateTime;
    }
}
