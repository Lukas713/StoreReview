<?php

namespace Inchoo\StoreReview\Controller\Adminhtml\Reviews;

use Inchoo\StoreReview\Api\Data\StoreReviewInterface;
use Inchoo\StoreReview\Api\StoreReviewRepositoryInterface;
use Inchoo\StoreReview\Model\ResourceModel\StoreReview\CollectionFactory;
use Magento\Backend\App\Action;

class MassSelectApprove extends Action
{
    const ADMIN_RESOURCE = 'Inchoo_StoreReview::reviews';

    /**
     * @var StoreReviewRepositoryInterface
     */
    private $storeReviewRepository;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param StoreReviewRepositoryInterface $storeReviewRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        StoreReviewRepositoryInterface $storeReviewRepository,
        CollectionFactory $collectionFactory
    )
    {
        parent::__construct($context);
        $this->storeReviewRepository = $storeReviewRepository;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $result = $this->getRequest()->getParam('selected');
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(StoreReviewInterface::STORE_REVIEW_ID, $result);
        foreach ($collection as $model) {
            $model->setApproved(true);
            $model->setSelected(true);
            $this->storeReviewRepository->save($model);
        }
        $this->messageManager->addSuccessMessage("Successful");
        return $this->_redirect("store_review/reviews/");
    }
}
