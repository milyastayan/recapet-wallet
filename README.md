# Recapet Wallet

A simple wallet system built with Laravel, supporting deposits, withdrawals, transfers (with optional fees), idempotency, concurrency safety, immutable ledger tracking, and balance snapshots.

---

## ⚙️ Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/milyastayan/recapet-wallet
   cd recapet-wallet

2. **Install dependencies**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate

3. **Configure environment**

    Set your database credentials in .env file.


4. **Run migrations and seeders**
    ```bash
    php artisan migrate --seed

📦 **Features**

✅ **Core Operations**  
- Deposit — Add balance to your wallet.  
- Withdraw — Remove balance (if sufficient funds).  
- Transfer — Send funds to another wallet with optional fees.  
- Fee Handling — Fees are applied automatically for transfers over $25.  
- Idempotency — Prevents duplicate transfer requests.  
- Concurrency Safety — Wallet balances are locked during critical operations to prevent race conditions.  
- Immutable Ledger — Every transaction creates a ledger entry. No updates or deletions.  
- Balance Snapshots — Periodic snapshots saved for auditing and reconciliation.

---

🔐 **Security Considerations**  
- Passwords are hashed using Laravel’s bcrypt algorithm.  
- Data is encrypted at rest (for sensitive columns) and in transit (HTTPS expected).  
- Rate-limiting is enabled for sensitive endpoints (e.g. auth, transfers).  
- Authenticated routes protected by Sanctum.  
- Wallet access is scoped per user.

💸 **Money Precision**
- All amounts are stored in cents as integers.
- Precision guaranteed using integer math.
- When displaying to users, values are formatted using:
  ```php
  number_format($amount / 100, 2)

🧪 **Testing**
- Run tests with:
  ```bash
  ./vendor/bin/pest

**Includes test cases for:**

- ✅ Deposits
- ✅ Withdrawals (with and without insufficient balance)
- ✅ Transfers (with and without fees)
- ✅ Idempotency handling
- ✅ Concurrency using parallel requests
- ✅ Ledger entries  

📬 **API Documentation**  
You’ll find a full Postman Collection inside:  
`docs/postman/recapetـwallet.postman_collection.json`

📘 **Example Endpoints**

| Method | Endpoint                 | Description     |
|--------|--------------------------|-----------------|
| POST   | /api/wallet/deposit      | Deposit funds   |
| POST   | /api/wallet/withdrawals | Withdraw funds  |
| POST   | /api/wallet/transfer     | Transfer funds  |

📘 **Design Notes**
- Wallet operations are grouped in a `HandlesWalletConcurrency` trait for clarity and reuse.
- Events are dispatched after each operation to trigger asynchronous processes (e.g. ledger entry creation).
- Ledger entries follow a simple, append-only model for auditability.
- Transfers use dual wallet locking to avoid deadlocks.
- Snapshots are created periodically (you can automate via a scheduled command).  

## Credits
This application was created by Yazan TAYAN. If you have any questions or feedback, please contact milyastayan@gmail.com.
