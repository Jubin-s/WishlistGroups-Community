<?php

namespace Egits\WishlistGroups\Controller\Index;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Egits\WishlistGroups\Model\SendWishlistMail;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\Data\CustomerExtensionFactory;

/**
 * Class Share
 *
 */
class Share implements ActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SendWishlistMail
     */
    protected $sendWishlistMail;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerExtensionFactory
     */
    protected $customerExtensionFactory;

    /**
     * Constructor
     * @param JsonFactory $jsonFactory
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param SendWishlistMail $sendWishlistMail
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param CustomerExtensionFactory $customerExtensionFactory
     */
    public function __construct(
        JsonFactory $jsonFactory,
        RequestInterface $request,
        LoggerInterface $logger,
        SendWishlistMail $sendWishlistMail,
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        CustomerExtensionFactory $customerExtensionFactory
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->sendWishlistMail = $sendWishlistMail;
        $this->messageManager = $context->getMessageManager();
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->customerExtensionFactory = $customerExtensionFactory;
    }

    /**
     * Execute method
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();

        // Get form data
        $formData = $this->request->getParam('itemData');
        $customerName = $this->request->getParam('customerName');
        $customerEmail = $this->request->getParam('customerEmail');

        $this->logger->error('\Customer Name: ' . $customerName);
        $this->logger->error('\Customer Email: ' . $customerEmail);
        // Prepare additional data
        $additionalData = [
            'customerName' => $customerName,
            'customerEmail' => $customerEmail,
        ];


        try {
            // Send survey email (if applicable)
            $this->logger->info('formdata ' . json_encode($formData));
            $this->sendWishlistMail->execute($formData, $additionalData);
            $this->messageManager->addSuccessMessage(__('Wishlist shared successfully'));

            // Return success response
            return $result->setData([
                'success' => true,
                'message' => __('Thank you for your feedback!'),
            ]);
        } catch (LocalizedException $e) {
            $this->logger->error('Survey Submission Error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Unable to share the wishlist'));
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Unexpected Error in Survey Submission: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Unable to share the wishlist'));
            return $result->setData([
                'success' => false,
                'message' => __('Unable to share the wishlist.')
            ]);
        }
    }
}
