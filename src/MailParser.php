<?php

namespace Esplora\Lumos;

use Illuminate\Support\Collection;
use ZBateson\MailMimeParser\Header\AddressHeader;
use ZBateson\MailMimeParser\Header\HeaderConsts;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;

class MailParser
{
    /**
     * The parsed message instance.
     *
     * @var \ZBateson\MailMimeParser\Message
     */
    protected Message $message;

    /**
     * Creates an instance of the parser and parses the given email content.
     *
     * @param string $content The email content to parse.
     */
    public function __construct(string $content)
    {
        $this->message = (new MailMimeParser)
            ->parse($content, true);
    }

    /**
     * Static method to create an instance from a string of email content.
     *
     * @param string $content                 The email content to parse.
     * @param bool   $enableCharsetConversion Whether to enable charset conversion.
     *
     * @return self
     */
    public static function fromString(string $content): self
    {
        return new self($content);
    }

    /**
     * Get the sender's email address.
     *
     * @return string|null The sender's email address or null if unavailable.
     */
    public function from(): ?string
    {
        return $this->message
            ->getHeader(HeaderConsts::FROM)
            ?->getEmail();
    }

    /**
     * Get the sender's name.
     *
     * @return string|null The sender's name or null if unavailable.
     */
    public function fromName(): ?string
    {
        return $this->message
            ->getHeader(HeaderConsts::FROM)
            ?->getPersonName();
    }

    /**
     * Get the list of recipients in the "To" field.
     *
     * Returns a collection where the key is the email address and the value is the person's name.
     *
     * @return \Illuminate\Support\Collection A collection of recipients in the "To" field.
     */
    public function to(): Collection
    {
        return $this->getRecipients(HeaderConsts::TO);
    }

    /**
     * Get the list of recipients in the "CC" field.
     *
     * Returns a collection where the key is the email address and the value is the person's name.
     *
     * @return \Illuminate\Support\Collection A collection of recipients in the "CC" field.
     */
    public function cc(): Collection
    {
        return $this->getRecipients(HeaderConsts::CC);
    }

    /**
     * Get the list of recipients in the "BCC" field.
     *
     * Returns a collection where the key is the email address and the value is the person's name.
     *
     * @return \Illuminate\Support\Collection A collection of recipients in the "BCC" field.
     */
    public function bcc(): Collection
    {
        return $this->getRecipients(HeaderConsts::BCC);
    }

    /**
     * Get the subject of the email.
     *
     * @return string|null The subject of the email or null if unavailable.
     */
    public function subject(): ?string
    {
        return $this->message
            ->getHeaderValue(HeaderConsts::SUBJECT);
    }

    /**
     * Get the date the email was sent.
     *
     * @return \DateTimeImmutable|null The date and time the email was sent or null if unavailable.
     */
    public function date(): ?\DateTimeImmutable
    {
        return $this->message
            ->getHeader(HeaderConsts::DATE)?->getDateTime();
    }

    /**
     * Get the text content of the email.
     *
     * @return string The text content of the email, or an empty string if none is available.
     */
    public function text(): string
    {
        return $this->message->getTextContent() ?? '';
    }

    /**
     * Get the HTML content of the email.
     *
     * @return string The HTML content of the email, or an empty string if none is available.
     */
    public function html(): string
    {
        return $this->message->getHtmlContent() ?? '';
    }

    /**
     * Get the attachments of the email.
     *
     * Returns a collection containing information about each attachment:
     * - 'name' => The filename,
     * - 'mime' => The MIME type,
     * - 'content' => The attachment's content.
     *
     * @return \Illuminate\Support\Collection A collection of email attachments.
     */
    public function attachments(): Collection
    {
        return collect($this->message->getAllAttachmentParts())
            ->map(fn (Message\MimePart $part) => [
                'name'    => $part->getFilename(),
                'mime'    => $part->getContentType(),
                'content' => $part->getContent(),
            ]);
    }

    /**
     * Get a list of recipients for a specific header field (TO, CC, BCC).
     *
     * This method is used internally by `to()`, `cc()`, and `bcc()`.
     *
     * @param string $header The header field (TO, CC, or BCC).
     *
     * @return \Illuminate\Support\Collection A collection of email addresses and names.
     */
    protected function getRecipients(string $header): Collection
    {
        return collect($this->message->getHeader($header)?->getAddresses() ?? [])
            ->mapWithKeys(fn (AddressHeader $address) => [$address->getEmail() => $address->getPersonName()]);
    }
}
