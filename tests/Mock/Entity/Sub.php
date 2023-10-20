<?php

declare(strict_types=1);

namespace HyperfTest\Mock\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sub')]
class Sub
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::INTEGER)]
    private int $value;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Sub
    {
        $this->id = $id;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): Sub
    {
        $this->value = $value;
        return $this;
    }
}
