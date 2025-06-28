<?php

/**
 * Wallet fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Wallet;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class WalletFixtures.
 */
class WalletFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * This method must return an array of fixture classes
     * on which the implementing class depends on.
     *
     * @return string[]
     *
     * @psalm-return array{0: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    protected function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(20, 'wallet', function (int $i) {
            $wallet = new Wallet();
            $wallet->setTitle($this->faker->unique()->word().' Wallet');
            $wallet->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $wallet->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $author = $this->getRandomReference('user', User::class);

            $wallet->setAuthor($author);
            // Domyślne saldo 0

            return $wallet;
        });
    }
}
