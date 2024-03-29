<?php

declare(strict_types = 1);

namespace Training\Feedback\Controller\Adminhtml\Index;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Training\Feedback\Api\Data\Feedback\FeedbackRepositoryInterface;
use Training\Feedback\Api\Data\Reply\ReplyRepositoryInterface;
use Training\Feedback\Model\ResourceModel\Feedback\CollectionFactory;

/**
 * Provides feedbacks mass deletion 
 */
class MassDelete implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Training_Feedback::feedback_delete';    
    
    private $messageManager;
    
    private $resultFactory; 
    
    private $filter;
    
    private $collectionFactory;
    
    private $feedbackRepository;

    private $replyRepository;
    
    private $logger;

    /**
     * 
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FeedbackRepositoryInterface $feedbackRepository
     * @param ReplyRepositoryInterface $replyRepository
     * @param LoggerInterface $logger
     */

    public function __construct(
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,    
        Filter                    $filter,
        CollectionFactory         $collectionFactory,
        FeedbackRepositoryInterface $feedbackRepository,
        ReplyRepositoryInterface $replyRepository,
        LoggerInterface           $logger = null
    ) {        
        $this->messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->feedbackRepository = $feedbackRepository;
        $this->replyRepository = $replyRepository;
        $this->logger = $logger;
    }

    /**
     * Mass Delete Action
     *
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        //counters for deleted feddbacks and possible errors
        $feedbackDeleted = $feedbackDeletedError = 0;        
        foreach ($collection as $feedback) {
            try {
                $this->feedbackRepository->delete($feedback);
                $this->replyRepository->deleteByFeedbackId($feedback->getFeedbackId());
                $feedbackDeleted++;
            } catch (LocalizedException $exception) {
                $this->logger->error($exception->getLogMessage());
                $feedbackDeletedError++;
            }
        }
        if ($feedbackDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $feedbackDeleted));
        }
        if ($feedbackDeletedError) {
            $this->messageManager->addErrorMessage(
                __('A total of %1 record(s) haven\'t been deleted. Please see server logs for more details.',
                    $feedbackDeletedError));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}