<?php


namespace Egits\WishlistGroups\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Egits\WishlistGroups\Block\Email\Grid\Wishlist;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * Class SendWishlistMail
 *
 */
class SendWishlistMail
{

    const XML_PATH_EMAIL_RECIPIENT = 'wishlist_groups/wishlist/sender';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Wishlist
     */
    private Wishlist $wishlistGrid;
    /**
     * @var State
     */
    private State $appState;
    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;
    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var SenderResolverInterface
     */
    private SenderResolverInterface $senderResolver;

    /**
     * SendWishlistMail constructor.
     *
     * @param SenderResolverInterface $senderResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Wishlist $wishlistGrid
     * @param State $appState
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SenderResolverInterface $senderResolver,
        ScopeConfigInterface $scopeConfig,
        Wishlist $wishlistGrid,
        State $appState,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager
    )
    {
        $this->senderResolver = $senderResolver;
        $this->scopeConfig = $scopeConfig;
        $this->wishlistGrid = $wishlistGrid;
        $this->appState = $appState;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
    }

    /**
     * Function to send request a quote email.
     *
     * @param $formData
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */

    public function execute($formData, $data)
    {
        $this->wishlistGrid->setEmailFormData($formData);
        $wishlistGrid = $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$this->wishlistGrid, 'toHtml']
        );
        $this->inlineTranslation->suspend();

        $customerName = $data['customerName'];
        $customerEmail = $data['customerEmail'];

        $sender = [
            'name' => $customerName,
            'email' => $customerEmail
        ];

        $recipient = $this->senderResolver->resolve($this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        ));
        $sentToEmail = $recipient['email'];
        $sentToName = $recipient['name'];

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('wishlist_email_template')
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
            )
            ->setTemplateVars([
                'customerName' => $customerName,
                'customerEmail' => $customerEmail,
                'wishlistGrid' => $wishlistGrid,
            ])
            ->setFromByScope($sender)
            ->addTo($sentToEmail, $sentToName)
            ->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();

    }
}
