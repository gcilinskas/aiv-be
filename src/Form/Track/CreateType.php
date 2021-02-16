<?php

namespace App\Form\Track;

use App\Entity\Track;
use App\Service\FileService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

/**
 * Class CreateType
 */
class CreateType extends AbstractType
{
    /**
     * @var FileService
     */
    private $fileService;

    /**
     * CreateType constructor.
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class, [
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File(
                    [
                        'mimeTypes' => ['audio/mpeg',  'audio/wav', 'audio/flac'],
                        'mimeTypesMessage' => 'api.error.audio_file.mime_type_not_valid',
                        'maxSize' => '1000M',
                        'maxSizeMessage' => 'api.error.audio_file.too_large',
                    ]
                )
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection'   => false,
        ]);
    }
}
