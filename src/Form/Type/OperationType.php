<?php

/**
 * Operation type.
 */

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Operation;
use App\Entity\Wallet;
use App\Form\DataTransformer\TagsDataTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use App\Repository\WalletRepository;

/**
 * Class OperationType.
 */
class OperationType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param TagsDataTransformer $tagsDataTransformer Tags data transformer
     */
    public function __construct(private readonly TagsDataTransformer $tagsDataTransformer)
    {
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.title',
                'translation_domain' => 'operation',
                'required' => true,
                'attr' => ['max_length' => 255],
            ]
        );
        if ($options['is_create']) {
            $builder->add(
                'amount',
                NumberType::class,
                [
                    'label' => 'label.amount',
                    'translation_domain' => 'operation',
                    'required' => true,
                    'scale' => 2,
                    'attr' => [
                        'step' => 0.01,
                        'min' => 0,
                    ],
                ]
            );
            if ($options['wallet_locked']) {
                $builder->add('wallet', HiddenType::class, [
                    'mapped' => false,
                ]);
            } else {
                $builder->add('wallet', EntityType::class, [
                    'class' => Wallet::class,
                    'translation_domain' => 'operation',
                    'choice_label' => 'title',
                    'label' => 'label.wallet',
                    'placeholder' => 'label.choose_wallet',
                    'required' => true,
                    'query_builder' => function (WalletRepository $repo) use ($user) {
                        return $repo->createQueryBuilder('w')
                            ->where('w.author = :author')
                            ->setParameter('author', $user);
                    },
                ]);
            }
        }
        $builder->add(
            'category',
            EntityType::class,
            [
                'class' => Category::class,
                'translation_domain' => 'operation',
                'choice_label' => function ($category): string {
                    return $category->getTitle();
                },
                'label' => 'label.category',
                'placeholder' => 'label.none',
                'required' => true,
            ]
        );
        $builder->add(
            'tags',
            TextType::class,
            [
                'label' => 'label.tags',
                'translation_domain' => 'operation',
                'required' => false,
                'attr' => ['max_length' => 128],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            $this->tagsDataTransformer
        );
        $builder->add('operationDescription', TextareaType::class, [
            'label' => 'label.description',
            'translation_domain' => 'operation',
            'required' => true,
            'attr' => [
                'rows' => 5, // np. większe pole
                'maxlength' => 2000, // jeśli chcesz ograniczyć długość
            ],
        ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
            'is_create' => false,
            'wallet_locked' => false,
            'user' => null,
        ]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     */
    public function getBlockPrefix(): string
    {
        return 'operation';
    }
}
