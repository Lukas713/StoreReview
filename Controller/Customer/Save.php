<?php

namespace Inchoo\StoreReview\Controller\Customer;

use Inchoo\StoreReview\Api\Data\StoreReviewInterface;
use Inchoo\StoreReview\Api\StoreReviewRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Redirecter
{
    /**
     * @var StoreReviewRepositoryInterface
     */
    private $storeReviewRepository;


    /**
     * @var Http
     */
    private $request;

    /**
     * Save constructor.
     * @param Context $context
     * @param StoreReviewRepositoryInterface $storeReviewRepository
     * @param Escaper $escaper
     * @param Session $session
     */
    public function __construct(
        Context $context,
        StoreReviewRepositoryInterface $storeReviewRepository,
        Session $session,
        Http $request,
        Validator $validator
    )
    {
        parent::__construct($context, $session, $validator);
        $this->storeReviewRepository = $storeReviewRepository;
        $this->session = $session;
        $this->request = $request;
    }

    public function execute()
    {
        $this->redirectIfNotLogged();
        $this->validateFormKey();
        $params = $this->request->getPost()->toArray();
        if (isset($params[StoreReviewInterface::STORE_REVIEW_ID])) {
            try {
                $model = $this->storeReviewRepository->getById($params[StoreReviewInterface::STORE_REVIEW_ID]);
                if ($model->getCustomer() != $this->session->getCustomerId()) {
                    $this->messageManager->addErrorMessage("Wrong entity id");
                    return $this->_redirect("store_review/customer");
                }
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $this->_redirect("store_review/customer");
            }
        }
        $this->storeReviewRepository->insertRecord($params);
        $ticketParams = [
            StoreReviewInterface::TITLE => $params[StoreReviewInterface::TITLE],
            StoreReviewInterface::CONTENT => $params[StoreReviewInterface::CONTENT],
            StoreReviewInterface::CUSTOMER => $this->session->getCustomerId()
        ];
        $this->_eventManager->dispatch(
            'inchoo_ticket_created',
            ['data' => $ticketParams]
        );
        $this->messageManager->addSuccessMessage("Successfully");
        return $this->_redirect("store_review/customer");
    }
}
