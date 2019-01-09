<?php
/**
 * Blackbird Phone Number Library
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category    Blackbird
 * @package     Blackbird_PhoneNumberLib
 * @copyright   Copyright (c) 2019 Blackbird (https://black.bird.eu)
 * @author      Blackbird Team
 * @license     https://store.bird.eu/license/
 * @support     help@bird.eu
 */
declare(strict_types=1);

namespace Blackbird\PhoneNumberLib\Validator;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberParseException;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Store\Model\ScopeInterface;

/**
 * Phone number validator which try to auto-detect the format with the current locale if possible.
 *
 * @api
 */
final class PhoneNumberValidator extends AbstractValidator
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     * @param null|string $countryCode [optional] Set the locale to validate with.
     */
    public function isValid($value, ?string $countryCode = null): bool
    {
        if (!$this->isValidPhoneNumber($value)) {
            $countryCode = $this->scopeConfig->getValue(
                DirectoryData::XML_PATH_DEFAULT_COUNTRY,
                ScopeInterface::SCOPE_STORE
            );

            return $this->isValidPhoneNumber($value, $countryCode);
        }

        return true;
    }

    /**
     * Check whether a phone number is valid or not
     *
     * @param string $phoneNumber
     * @param null|string $countryCode
     * @return bool
     */
    private function isValidPhoneNumber(string $phoneNumber, ?string $countryCode = null): bool
    {
        $this->_clearMessages();

        try {
            $phone = PhoneNumber::parse($phoneNumber, $countryCode);

            if (!$phone->isValidNumber()) {
                $this->_addMessages([new Phrase('Invalid phone number format.')]);

                return false;
            }
        } catch (PhoneNumberParseException $e) {
            $this->_addMessages([$e->getMessage()]);

            return false;
        }

        return true;
    }
}
