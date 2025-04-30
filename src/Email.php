<?php

namespace Bermuda\Stdlib;

use Bermuda\Stdlib\StrHelper;

/**
 * Class Email
 *
 * Represents an immutable email address and provides utility methods for
 * normalization, validation, comparison, obfuscation, and domain/user matching.
 *
 * This class implements the \Stringable interface so that the object can be used as a string.
 */
final class Email implements \Stringable
{
    /**
     * Computed property to extract and return the domain part of the email address.
     * For example, if the email is "user@example.com", this property returns "example.com".
     *
     * The getter uses strrchr() to locate the last occurrence of '@',
     * and substr() removes the '@' character from the returned substring.
     */
    private string $domain {
        get {
            return substr(strrchr($this->value, '@'), 1);
        }
    }

    /**
     * Computed property to extract and return the local (user) part of the email address.
     * For example, if the email is "user@example.com", this property returns "user".
     *
     * The getter uses strstr() with the third parameter set to true so that it returns
     * the portion of the string before '@'.
     */
    private string $user {
        get {
            return strstr($this->value, '@', true);
        }
    }

    /**
     * Constructs an immutable Email object.
     *
     * @param string $value The complete email address.
     *
     * The readonly property ensures that the Email object remains immutable once constructed.
     */
    public function __construct(
        public readonly string $value
    ) {
        // No additional initialization needed.
    }

    /**
     * Factory method for creating an Email object with validation.
     *
     * This method normalizes the provided email (trims leading/trailing spaces
     * and converts to lowercase), then verifies its format using the isValid() method.
     * If the email format is invalid, an InvalidArgumentException is thrown.
     *
     * @param string $email The input email address.
     * @return Email A validated and normalized Email object.
     * @throws \InvalidArgumentException If the email format is invalid.
     */
    public static function createWithValidation(string $email): Email
    {
        // Normalize the email by removing whitespace and converting it to lowercase.
        $normalized = strtolower(trim($email));

        // Validate the normalized email.
        if (!self::isValid($normalized)) {
            throw new \InvalidArgumentException('Invalid email: ' . $email);
        }

        return new Email($normalized);
    }

    /**
     * Validates an email address using PHP's filter_var() function.
     *
     * @param string $email The email address to validate.
     * @return bool Returns true if the email address is valid, false otherwise.
     */
    public static function isValid(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Normalizes an email address by trimming whitespace and converting to lowercase.
     *
     * @param string $email The email address to normalize.
     * @return string The normalized email address.
     */
    public static function normalize(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Compares the current Email object to another for equality.
     *
     * @param Email $email The Email object to compare against.
     * @return bool Returns true if both Email objects have the same email value.
     */
    public function equals(Email $email): bool
    {
        return $this->value === $email->value;
    }

    /**
     * Obfuscates the email address to improve privacy.
     *
     * The method uses a match expression to determine how the local part (username) should
     * be obfuscated based on its length:
     *
     * - If the local part is 3 characters or less: all characters are replaced by asterisks.
     * - If the local part is exactly 4 characters: show the first character and mask the rest.
     * - If the local part is between 5 and 7 characters: retain the first and last characters,
     *   and mask the in-between characters.
     * - If the local part is 8 characters or longer: retain the first two and last two characters,
     *   and mask the remaining middle characters.
     *
     * In all cases, str_repeat() is used to generate the required number of asterisks.
     *
     * @return string The obfuscated email address.
     */
    public function obfuscate(): string
    {
        $obfuscated = match(true) {
            ($length = strlen($this->user)) <= 3 => str_repeat('*', $length),
            $length == 4 => $this->user[0] . str_repeat('*', 3),
            $length > 4 && $length < 8 => $this->user[0] . str_repeat('*', $length - 2) . $this->user[$length -1],
            $length >= 8 => substr($this->user, 0, 2) . str_repeat('*', $length - 4) . substr($this->user, -2),
        };

        return $obfuscated . '@' . $this->domain;
    }

    /**
     * Checks if the current Email object matches any in a given list of Email objects.
     *
     * @param Email[] $emails An array of Email objects to compare against.
     * @return bool Returns true if the current email equals any email in the array.
     */
    public function equalsAny(array $emails): bool
    {
        // array_any() is assumed to check if at least one array element meets the condition.
        return array_any($emails, static fn(Email $email) => $this->equals($email));
    }

    /**
     * Returns the email address as a string.
     *
     * @return string The email address.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Checks if the email's domain matches a specific domain or any domain in a given list.
     *
     * @param string|string[] $domains A single domain (or an array of domains) to check.
     * @return bool Returns true if the email's domain matches one of the provided domains.
     */
    public function matchDomain(string|array $domains): bool
    {
        return StrHelper::equals($this->domain, $domains);
    }

    /**
     * Checks if the email's local (user) part matches a specific string or any in a given list.
     *
     * @param string|string[] $users A single user string (or an array of user strings) to check.
     * @return bool Returns true if the email's local part matches one of the provided users.
     */
    public function matchUser(string|array $users): bool
    {
        return StrHelper::equals($this->user, $users);
    }
}

