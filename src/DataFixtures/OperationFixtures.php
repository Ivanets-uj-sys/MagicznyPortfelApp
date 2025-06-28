<?php

/**
 * Operation fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\Tag;
use App\Entity\Wallet;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class OperationFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class OperationFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }
        $wallets = $this->getAllReferences('wallet', Wallet::class);
        $minBalance = 1.00;

        foreach ($wallets as $wallet) {
            $operations = [];
            $balance = $this->faker->randomFloat(2, 100, 500); // Początkowe saldo portfela

            for ($i = 0; $i < 20; ++$i) {
                $createdAtMutable = $this->faker->dateTimeBetween('-100 days', '-10 days');
                $updatedAtMutable = $this->faker->dateTimeBetween($createdAtMutable, 'now');
                $createdAt = \DateTimeImmutable::createFromMutable($createdAtMutable);
                $updatedAt = \DateTimeImmutable::createFromMutable($updatedAtMutable);
                $latestTime = max($createdAt, $updatedAt);

                $maxWithdrawal = max(0, $balance - $minBalance);

                if ($this->faker->boolean(60)) {
                    // Dochód
                    $amount = $this->faker->randomFloat(2, 0.01, 50);
                } else {
                    // Wydatek – ale nie większy niż dozwolony
                    if ($maxWithdrawal >= 0.01) {
                        $amount = -$this->faker->randomFloat(2, 0.01, min(50, $maxWithdrawal));
                    } else {
                        $amount = 0.0; // nie stać nas
                    }
                }
                $operations[] = [
                    'amount' => $amount,
                    'createdAt' => $createdAt,
                    'updatedAt' => $updatedAt,
                    'latestTime' => $latestTime,
                    'title' => $this->faker->words(3, true),
                    'description' => $this->faker->sentence(),
                ];
            }

            usort($operations, fn ($a, $b) => $a['latestTime'] <=> $b['latestTime']);

            foreach ($operations as $data) {
                $balance += $data['amount'];
                $balance = round($balance, 2);

                $operation = new Operation();
                $operation->setAmount($data['amount']);
                $operation->setBalance($balance);
                $operation->setTitle($data['title']);
                $operation->setOperationDescription($data['description']);
                $operation->setCreatedAt($data['createdAt']);
                $operation->setUpdatedAt($data['updatedAt']);
                $operation->setWallet($wallet);

                $category = $this->getRandomReference('category', Category::class);
                $operation->setCategory($category);

                $tags = $this->getRandomReferenceList('tag', Tag::class, $this->faker->numberBetween(0, 5));
                foreach ($tags as $tag) {
                    /* @var Tag $tag */
                    $operation->addTag($tag);
                }

                $this->manager->persist($operation);
            }
        }

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class, 1: TagFixture::class, 2: WalletFixture::class}
     */
    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            TagFixtures::class,
            WalletFixtures::class,
        ];
    }
}
