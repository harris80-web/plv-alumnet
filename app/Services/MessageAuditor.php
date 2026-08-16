<?php

namespace App\Services;

use App\Models\ChatbotSetting;

/**
 * Scans alumni-to-alumni message text for a small set of high-signal
 * scam/phishing indicators. Deliberately rule-based rather than an AI call:
 * it runs on every message send, so it needs to be instant and free, and
 * these three categories (money requests, personal-info requests, external
 * links) are pattern-detectable — an LLM call would add latency/cost to
 * every message for no real accuracy gain here. See MessageFlag for what
 * happens with a match.
 */
class MessageAuditor
{
    private const MONEY_TRANSFER_KEYWORDS = [
        'gcash', 'paymaya', 'maya wallet', 'palawan pawnshop', 'western union',
        'bank transfer', 'padala', 'send money', 'send.*cash', 'send.*load',
        'bpi', 'bdo', 'metrobank', 'unionbank', 'landbank',
    ];

    private const PERSONAL_INFO_KEYWORDS = [
        'password', 'otp', 'one[- ]time pin', 'cvv', 'pin number',
        'student id number', 'account number', 'verification code',
    ];

    /** @return string[] matched category keys — see App\Models\MessageFlag::REASONS */
    public function scan(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [];
        }

        $settings = ChatbotSetting::current();
        $reasons = [];

        if ($settings->money_transfer_detection && $this->matchesMoneyTransfer($content)) {
            $reasons[] = 'money_transfer';
        }
        if ($settings->personal_info_detection && $this->matchesPersonalInfo($content)) {
            $reasons[] = 'personal_info';
        }
        if ($settings->external_link_detection && $this->matchesExternalLink($content)) {
            $reasons[] = 'external_link';
        }

        return $reasons;
    }

    private function matchesMoneyTransfer(string $content): bool
    {
        if ($this->matchesAny($content, self::MONEY_TRANSFER_KEYWORDS)) {
            return true;
        }

        // A peso amount on its own ("₱500", "P500", "500 pesos", "PHP 500").
        return (bool) preg_match('/(₱|php\s?|(?<![a-z])p)\s?\d{2,}|(\d{2,}\s?(pesos|php))/i', $content);
    }

    private function matchesPersonalInfo(string $content): bool
    {
        return $this->matchesAny($content, self::PERSONAL_INFO_KEYWORDS);
    }

    private function matchesExternalLink(string $content): bool
    {
        return (bool) preg_match('/\b(https?:\/\/|www\.)\S+/i', $content);
    }

    private function matchesAny(string $content, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (preg_match('/\b' . $keyword . '\b/i', $content)) {
                return true;
            }
        }
        return false;
    }
}
