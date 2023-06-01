<?php

declare(strict_types=1);

namespace HyperfTest\Mock\Entity;

use Doctrine\ORM\Mapping as ORM;
use HyperfTest\Mock\Repository\UserRepository;

#[ORM\Entity(UserRepository::class)]
#[ORM\Table(name: 'user')]
class UserWithRepository extends AbstractUser
{
}
