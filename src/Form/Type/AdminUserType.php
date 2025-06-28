<?php

/** Damin User Type. */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email as EmailAssert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\User;

/**
 * Class AdminUserType.
 */
class AdminUserType extends AbstractType
{
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
        $isEdit = $options['is_edit'];

        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('email', EmailType::class, [
                'mapped' => true,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'change_password.error.email_blank']),
                    new EmailAssert(['message' => 'change_password.error.invalid_email']),
                ],
            ]);

        if ($isEdit) {
            // Podczas edycji hasło jest opcjonalne
            $builder->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => false,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'change_password.error.passwords_must_match',
                'constraints' => [
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'change_password.error.new_password_too_short',
                        'max' => 4096,
                    ]),
                ],
            ]);

            $builder->add('currentPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Current Password',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'current_password.required']),
                ],
            ]);
        } else {
            // Podczas tworzenia hasło jest wymagane
            $builder->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'invalid_message' => 'change_password.error.passwords_must_match',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'change_password.error.new_password_blank']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'change_password.error.new_password_too_short',
                        'max' => 4096,
                    ]),
                ],
            ]);
        }
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false, // domyślnie formularz do tworzenia
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
        return 'user';
    }
}
