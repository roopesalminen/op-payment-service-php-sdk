<?php

use CheckoutFinland\SDK\Client;
use CheckoutFinland\SDK\Model\CallbackUrl;
use CheckoutFinland\SDK\Request\EmailRefundRequest;
use CheckoutFinland\SDK\Request\RefundRequest;
use CheckoutFinland\SDK\Response\PaymentResponse;

/**
 * Class Refund
 */
class Refund
{

    /**
     * Handle refund data and create refund with SDK client
     *
     * @param array $data
     *
     * @return PaymentResponse|string
     */
    public function processRefund($data)
    {
        try {
            $errorMsg = null;

            $client = new Client(
                375917,
                'SAIPPUAKAUPPIAS',
                [
                    'cofPluginVersion' => 'php-sdk-test-1.0.0',
                ]
            );
            $refund = new RefundRequest();

            $this->setRefundData($refund, $data);

        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            return $errorMsg;
        }

        try {
            $response = $client->refund($refund);

            return $response;

        } catch (RequestException $e) {
            // Fallback to email refund method if payment provider does not support online refunds
            if ($e->getCode() === 422) {

                $emailRefund = new EmailRefundRequest();
                $this->setEmailRefundData($emailRefund, $data);

                $response = $client->emailRefund($emailRefund);

                return $response;
            }
            $errorMsg = $e->getMessage();

        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }

        return $errorMsg;
    }

    /**
     * Set refund request data
     *
     * @param RefundRequest $refund
     * @param array $data
     *
     * @return RefundRequest
     */
    private function setRefundData($refund, $data) {

        $refund->setAmount($data['amount']);

        $callback = $this->createRefundCallbackUrl();
        $refund->setCallbackUrls($callback);

        return $refund;
    }

    /**
     * Set email refund request data
     *
     * @param EmailRefundRequest $emailRefund
     * @param array $data
     *
     * @return EmailRefundRequest
     */
    private function setEmailRefundData($emailRefund, $data) {

        $emailRefund->setEmail($data['email']);

        $emailRefund->setAmount($data['amount']);

        $callback = $this->createRefundCallbackUrl();
        $emailRefund->setCallbackUrls($callback);

        return $emailRefund;
    }

    /**
     * Set refund callback urls
     *
     * @return CallbackUrl
     */
    private function createRefundCallbackUrl() {

        $callback = new CallbackUrl();

        $callback->setSuccess('refund_callback_success_url');
        $callback->setCancel('refund_callback_cancel_url');

        return $callback;
    }

}