<?php


namespace App\Emails\Components;


class QuarantineEmailAttachment extends EmailComponent
{
    /**
     * Name
     * @var string
     */
    private $name = "";

    /**
     * Type
     * @var string
     */
    private $type = "";

    /**
     * Size in bytes (can always be null)
     * @var null|float
     */
    private $bytes = null;

    /**
     * Attachment constructor
     * @param string $name
     * @param string $type
     * @param float|null $bytes
     */
    public function __construct(string $name, string $type, ?float $bytes) {

        // Set attributes
        $this->name = $name;
        $this->type = $type;
        $this->bytes = $bytes;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Get type
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Get size
     * @return float|null
     */
    public function getBytes(): ?float {
        return $this->bytes;
    }
}
