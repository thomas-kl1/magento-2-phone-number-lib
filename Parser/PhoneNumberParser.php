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

namespace Blackbird\PhoneNumberLib\Parser;

use Brick\PhoneNumber\PhoneNumber;
use Brick\PhoneNumber\PhoneNumberFormat;
use Brick\PhoneNumber\PhoneNumberParseException;
use Magento\Directory\Helper\Data as DirectoryData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

/**
 * This class allows to parse a phone number to a specific allowed format.
 *
 * @api
 */
final class PhoneNumberParser
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
     * Parse the phone number to a specific format
     *
     * @param string $phoneNumber
     * @param null|string $countryCode
     * @param int $format [optional]
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    public function parse(
        string $phoneNumber,
        ?string $countryCode = null,
        int $format = PhoneNumberFormat::E164
    ): string {
        try {
            $phone = $this->parsePhoneNumber($phoneNumber, $countryCode, $format);
        } catch (PhoneNumberParseException $e) {
            if ($countryCode !== null) {
                throw new InputException(new Phrase('The phone number supplied does not match any format.'), $e);
            }

            $phone = $this->parse(
                $phoneNumber,
                $this->scopeConfig->getValue(DirectoryData::XML_PATH_DEFAULT_COUNTRY, ScopeInterface::SCOPE_STORE),
                $format
            );
        }

        return $phone;
    }

    /**
     * Try to parse the phone number with the locale
     *
     * @param string $phoneNumber
     * @param null|string $countryCode
     * @param int $format
     * @return string
     * @throws \Brick\PhoneNumber\PhoneNumberParseException
     */
    private function parsePhoneNumber(
        string $phoneNumber,
        ?string $countryCode = null,
        int $format = PhoneNumberFormat::E164
    ): string {
        $phone = PhoneNumber::parse($phoneNumber, $countryCode);

        if (!$phone->isValidNumber()) {
            throw new PhoneNumberParseException('Invalid Phone Number');
        }

        return $phone->format($format);
    }
}
