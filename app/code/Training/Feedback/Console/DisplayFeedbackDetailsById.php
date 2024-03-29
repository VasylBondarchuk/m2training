<?php

namespace Training\Feedback\Console;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Training\Feedback\Api\Data\Feedback\FeedbackRepositoryInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * bin/magento feedback:display --feedbackId=71
 */
class DisplayFeedbackDetailsById extends Command
{
    /**
     *
     */
    const FEEDBACK_ID = 'feedbackId';

    /**
     * @var FeedbackRepositoryInterface
     */
    private $feedbackRepository;

    /**
     * @param FeedbackRepositoryInterface $feedbackRepository
     */
    public function __construct(
        FeedbackRepositoryInterface $feedbackRepository
    )
    {
        $this->feedbackRepository = $feedbackRepository;
        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::FEEDBACK_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Feedback ID'
            )
        ];

        $this->setName('feedback:display')
            ->setDescription('Display feedbacks details by its id')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $feedbackId = (int)$input->getOption(self::FEEDBACK_ID);

        try {
            if(!$this->isEnteredIdValid($feedbackId)){
                $this->displayErrorMessage($output,'Please enter correct id. Id must be an integer positive number!');
            }
            else{
                $table = new Table($output);
                $table->setHeaders(['ID', 'Author name', 'Author Email', 'Status', 'Created' , 'Modified']);
                $feedback = $this->feedbackRepository->getById($feedbackId);
                $table->addRow([
                    $feedback->getFeedbackId(),
                    $feedback->getAuthorName(),
                    $feedback->getAuthorEmail(),
                    $feedback->getIsPublished(),
                    $feedback->getCreationTime(),
                    $feedback->getUpdateTime(),
                ]);
                $table->render();
            }
        } catch (LocalizedException $exception) {
            $this->displayErrorMessage($output,'There is no feedback, corresponding to entered id');
        }
        return $this;
    }

    /**
     * @param $feedbackId
     * @return bool
     */
    private function isEnteredIdValid ($feedbackId) : bool
    {
        return (is_int($feedbackId) && $feedbackId > 0);
    }

    /**
     * @param OutputInterface $output
     * @param string $errorMessage
     * @return void
     */
    private function displayErrorMessage (OutputInterface $output, string $errorMessage)
    {
        $output->writeln('<error>'.$errorMessage.'<error>');
    }
}
