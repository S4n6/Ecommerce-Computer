<?php

namespace App\Containers\AppSection\Order\Tasks;

use App\Containers\AppSection\Order\Models\Order;
use App\Containers\AppSection\Order\Repositories\OrderRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * CancelMyOrderTask
 *
 * Cancels a customer's order if it belongs to them and is still pending.
 */
class CancelMyOrderTask
{
    public function __construct(
        private readonly OrderRepository $repository,
    ) {}

    public function run(int $orderId, int $userId): Order
    {
        /** @var Order $order */
        $order = $this->repository->find($orderId);

        if ((int) $order->user_id !== $userId) {
            throw new AccessDeniedHttpException('You are not allowed to cancel this order.');
        }

        if ($order->status !== 'pending') {
            throw new BadRequestHttpException('Order cannot be cancelled');
        }

        /** @var Order $updated */
        $updated = $this->repository->update(['status' => 'cancelled'], $orderId);

        return $updated->load('items.product');
    }
}
