<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Tests\Unit\Services\Discount;

use Assert\LazyAssertionException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Discounts\Exceptions\UnsupportedDiscountTypeException;
use Tipoff\Discounts\Models\Discount;
use Tipoff\Discounts\Tests\Support\Models\TestSellable;
use Tipoff\Discounts\Tests\TestCase;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\TestSupport\Models\User;

class CalculateAdjustmentsTest extends TestCase
{
    use DatabaseTransactions;

    private TestSellable $sellable;
    private Cart $cart;

    public function setUp(): void
    {
        parent::setUp();

        TestSellable::createTable();
        $this->sellable = TestSellable::factory()->create();
        $this->cart = Cart::factory()->create();
    }

    /** @test */
    public function calculate_discount_with_no_discounts()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(0, $cart->getItemAmount()->getDiscounts());
    }

    /** @test */
    public function calculate_discount_with_order_discounts()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            /** @var Discount $discount */
            $discount = Discount::factory()->amount()->expired(false)->create([
                'code' => 'TESTCODE',
                'amount' => 1000,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $discount->applyToCart($this->cart);
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(1000, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(1500, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(1000, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(1500, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function calculate_discount_with_multiple_items()
    {
        $this->withCart([
            [2500, 1],
            [3500, 1],
        ], function ($cart) {
            /** @var Discount $discount */
            $discount = Discount::factory()->amount()->expired(false)->create([
                'code' => 'TESTCODE',
                'amount' => 1000,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $discount->applyToCart($this->cart);
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(2000, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(4000, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(1000, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(1500, $cartItem->getAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-1');
        $this->assertEquals(1000, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(2500, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function calculate_percent_discount()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            /** @var Discount $discount */
            $discount = Discount::factory()->percent()->expired(false)->create([
                'code' => 'TESTCODE',
                'percent' => 10,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $discount->applyToCart($this->cart);
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(250, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(2250, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(250, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(2250, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function ensure_discount_is_capped()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            /** @var Discount $code1 */
            $code1 = Discount::factory()->amount()->expired(false)->create([
                'code' => 'CODE1',
                'amount' => 2000,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $code1->applyToCart($this->cart);

            /** @var Discount $code2 */
            $code2 = Discount::factory()->amount()->expired(false)->create([
                'code' => 'CODE2',
                'amount' => 2000,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $code2->applyToCart($this->cart);
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(2500, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(0, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(2500, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(0, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function ensure_amount_off_is_before_percent_off()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            /** @var Discount $code1 */
            $code1 = Discount::factory()->amount()->expired(false)->create([
                'code' => 'CODE1',
                'amount' => 1500,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $code1->applyToCart($this->cart);

            /** @var Discount $code2 */
            $code2 = Discount::factory()->percent()->expired(false)->create([
                'code' => 'CODE2',
                'percent' => 50,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $code2->applyToCart($this->cart);
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(2000, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(500, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(2000, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(500, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function ensure_multiple_percent_off_use_discounted_value()
    {
        $this->withCart([
            [2000, 1],
        ], function ($cart) {
            /** @var Discount $code1 */
            $code1 = Discount::factory()->percent()->expired(false)->create([
                'code' => 'CODE1',
                'percent' => 50,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $code1->applyToCart($this->cart);

            /** @var Discount $code2 */
            $code2 = Discount::factory()->percent()->expired(false)->create([
                'code' => 'CODE2',
                'percent' => 50,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            $code2->applyToCart($this->cart);
            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(1500, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(500, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(1500, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(500, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function calculate_discount_with_participant_discounts()
    {
        $this->withCart([
            [5500, 1],
        ], function ($cart) {
            /** @var Discount $discount */
            $discount = Discount::factory()->amount()->expired(false)->create([
                'code' => 'TESTCODE',
                'amount' => 1000,
                'applies_to' => AppliesTo::PARTICIPANT(),
            ]);

            $discount->applyToCart($cart);

            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(4000, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(1500, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(4000, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(1500, $cartItem->getAmount()->getDiscountedAmount());
    }

    /** @test */
    public function calculate_discount_with_multiple_discounts()
    {
        $this->withCart([
            [5500, 1],
        ], function ($cart) {
            /** @var Discount $orderCode */
            $orderCode = Discount::factory()->amount()->expired(false)->create([
                'code' => 'CODE1',
                'amount' => 1000,
                'applies_to' => AppliesTo::ORDER(),
            ]);

            /** @var Discount $participantCode */
            $participantCode = Discount::factory()->amount()->expired(false)->create([
                'code' => 'CODE2',
                'amount' => 1000,
                'applies_to' => AppliesTo::PARTICIPANT(),
            ]);

            $orderCode->applyToCart($cart);
            $participantCode->applyToCart($cart);

            Discount::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(5000, $cart->getItemAmount()->getDiscounts());
        $this->assertEquals(500, $cart->getItemAmount()->getDiscountedAmount());

        $cartItem = $cart->findItem($this->sellable, 'item-0');
        $this->assertEquals(5000, $cartItem->getAmount()->getDiscounts());
        $this->assertEquals(500, $cartItem->getAmount()->getDiscountedAmount());
    }

    private function addCartItems(array $items): Cart
    {
        foreach ($items as $idx => $item) {
            [$amount, $quantity] = $item;

            $this->cart->upsertItem(
                Cart::createItem($this->sellable, "item-{$idx}", $amount, $quantity)
            );
        }

        return $this->cart;
    }

    private function withCart(array $items, \Closure $closure)
    {
        $result = ($closure)($this->addCartItems($items));

        // Save results so we can inspect
        $this->cart->cartItems->each->save();
        $this->cart->save();

        return $result;
    }
}
