# Email Class

A robust, immutable Email class for PHP 8.4+ that provides comprehensive email address handling with validation, normalization, and utility functions.

## Overview

The `Email` class is part of the `Bermuda\Stdlib` namespace and implements PHP's `\Stringable` interface. It encapsulates an email address as an immutable value object with powerful features for:

- Email validation
- Normalization (lowercase and whitespace trimming)
- Domain and username extraction
- Comparison and equality checking
- Privacy-focused obfuscation
- Domain and user matching

## Requirements

- PHP 8.4 or higher
- Bermuda\Stdlib package (for the StrHelper dependency)

## Installation

```bash
composer require bermudaphp/email
```

## Usage Examples

### Basic Instantiation

```php
use Bermuda\Stdlib\Email;

// Simple construction (no validation)
$email = new Email('user@example.com');

// With validation
try {
    $email = Email::createWithValidation('user@example.com');
} catch (\InvalidArgumentException $e) {
    // Handle invalid email
}
```

### Validation

```php
// Static validation
if (Email::isValid('test@example.com')) {
    // Email is valid
}

// Normalize an email string
$normalized = Email::normalize(' User@Example.COM '); // Returns "user@example.com"
```

### Email Properties

```php
$email = new Email('john.doe@example.com');

echo $email->domain; // Outputs: "example.com"
echo $email->user;   // Outputs: "john.doe"
echo $email->value;  // Outputs: "john.doe@example.com"
```

### Email Comparison

```php
$email1 = new Email('user@example.com');
$email2 = new Email('user@example.com');
$email3 = new Email('other@example.com');

$email1->equals($email2); // Returns: true
$email1->equals($email3); // Returns: false

// Check against multiple emails
$email1->equalsAny([$email2, $email3]); // Returns: true
```

### Email Obfuscation

The `obfuscate()` method intelligently masks parts of the email address for privacy:

```php
$email = new Email('john.doe@example.com');
echo $email->obfuscate(); // Outputs: "jo*****oe@example.com"

$email = new Email('joe@example.com');
echo $email->obfuscate(); // Outputs: "j**@example.com"

$email = new Email('bo@example.com');
echo $email->obfuscate(); // Outputs: "**@example.com"
```

### Domain and User Matching

```php
$email = new Email('user@example.com');

// Check if email belongs to specific domain(s)
$email->matchDomain('example.com'); // Returns: true
$email->matchDomain(['example.com', 'example.org']); // Returns: true
$email->matchDomain('gmail.com'); // Returns: false

// Check if email has specific username(s)
$email->matchUser('user'); // Returns: true
$email->matchUser(['admin', 'user']); // Returns: true
$email->matchUser('other'); // Returns: false
```

### String Conversion

The class implements `\Stringable`, so it can be used directly in string contexts:

```php
$email = new Email('user@example.com');
echo $email; // Outputs: "user@example.com"
```

## Key Features

### Immutability

The `Email` class is immutable, which means the email value cannot be changed after an instance is created. This ensures thread safety and prevents unexpected mutations.

### Computed Properties

The class leverages PHP 8.4's computed properties feature to dynamically extract the domain and user parts of the email when needed:

```php
private string $domain {
    get {
        return substr(strrchr($this->value, '@'), 1);
    }
}

private string $user {
    get {
        return strstr($this->value, '@', true);
    }
}
```

### Smart Obfuscation Algorithm

The `obfuscate()` method uses a sophisticated algorithm to mask parts of the email address based on the length of the username:

- 3 or fewer characters: All characters are masked (`***@example.com`)
- 4 characters: First character visible (`u***@example.com`)
- 5-7 characters: First and last characters visible (`u****r@example.com`) 
- 8+ characters: First two and last two characters visible (`us****er@example.com`)

## Best Practices

- Use `createWithValidation()` when handling user input to ensure valid email addresses
- Use the immutable nature of this class to safely pass email objects throughout your application
- For privacy concerns, use the `obfuscate()` method when displaying email addresses publicly
- Leverage the computed properties to access parts of the email without manual parsing
