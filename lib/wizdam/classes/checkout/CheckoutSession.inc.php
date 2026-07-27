<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/CheckoutSession.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class CheckoutSession 
 * 
 * @brief Data object sesi checkout 3-tahap WIZDAM (Cart -> Billing -> Payment).
 * Sengaja dipisah dari CartItemDAO/cart_items (yang melayani alur belanja
 * multi-item bergaya toko milik CartService) -- ini domain berbeda: satu
 * transaksi biaya naskah per sesi, dengan alamat penagihan.
 */

class CheckoutSession  {

    /** @var int $sessionId */
    private int $sessionId = 0;

    /** @var int $journalId */
    private int $journalId = 0;

    /** @var int $userId */
    private int $userId = 0;

    /** @var int $assocId */
    private int $assocId = 0;

    /** @var string $type */
    private string $type = '';

    /** @var float $amount */
    private float $amount = 0.0;

    /** @var string $currencyCode */
    private string $currencyCode = 'USD';

    /** @var array $payload */
    private array $payload = [];

    /**
     * Constructor
     */
    public function __construct(float $amount = 0.0, string $currencyCode = 'USD', int $userId = 0, int $assocId = 0) {
        $this->amount = $amount;
        $this->currencyCode = $currencyCode;
        $this->userId = $userId;
        $this->assocId = $assocId;
    }

    /**
     * Get Session id
     */
    public function getSessionId(): int { 
        return $this->sessionId; 
    }

    /**
     * Set Session id
     */
    public function setSessionId(int $sessionId): void { 
        $this->sessionId = $sessionId;
    }

    /** 
     * Alias supaya kompatibel dengan pola getId() gaya QueuedPayment lama 
     */
    public function getId(): int { 
        return $this->sessionId;
    }

    /**
     * Get journal id
     */
    public function getJournalId(): int { 
        return $this->journalId;
    }
    
    /**
     * Set journal id
     * @param mixed $journalId
     */
    public function setJournalId($journalId): void { 
        $this->journalId = (int) $journalId;
    }

    /**
     * Get user id
     */
    public function getUserId(): int { 
        return $this->userId;
    }

    /**
     * Set user id
     * @param mixed $userId
     */
    public function setUserId($userId): void { 
        $this->userId = (int) $userId;
    }

    /**
     * Get assoc id
     */
    public function getAssocId(): int { 
        return $this->assocId;
    }
    
    /**
     * Set Assoc id
     * @param mixed $assocId
     */
    public function setAssocId($assocId): void { 
        $this->assocId = (int) $assocId;
    }

    /**
     * Get type
     */
    public function getType() { 
        return $this->type;
    }
    
    /**
     * Set type
     * @param mixed $type
     */
    public function setType($type): void { 
        $this->type = (string) $type;
    }

    /**
     * Get amount
     */
    public function getAmount(): float { 
        return $this->amount;
    }
    
    /**
     * Set amount
     * @param float $amount
     */
    public function setAmount(float $amount): void { 
        $this->amount = $amount;
    }

    /**
     * Get currency code
     */
    public function getCurrencyCode(): string { 
        return $this->currencyCode;
    }

    /**
     * Set currency code
     * @param string $currencyCode
     */
    public function setCurrencyCode(string $currencyCode): void { 
        $this->currencyCode = $currencyCode;
    }

    /**
     * Get Checkout payload
     */
    public function getCheckoutPayload(): array { 
        return $this->payload;
    }
    
    /**
     * Set checkout payload
     * @param array $payload
     */
    public function setCheckoutPayload(array $payload): void { 
        $this->payload = $payload;
    }
    
}
?>