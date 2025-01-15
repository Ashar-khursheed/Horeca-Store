<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Square Payment Form</title>
</head>
<body>
    <h1>Payment Form</h1>
    <form id="payment-form">
        <div id="card-container"></div>
        <button id="card-button">Pay Now</button>
    </form>
    <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
    <script>
        const appId = "{{ env('SQUARE_APPLICATION_ID') }}";
        const locationId = "{{ env('SQUARE_LOCATION_ID') }}";

        async function initializeCard(payments) {
            const card = await payments.card();
            await card.attach('#card-container');
            return card;
        }

        async function createPayment() {
            const body = JSON.stringify({
                amount: 15,
                currency: 'USD',
                nonce: 'test-nonce',
            });

            const response = await fetch('/api/payment-square', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body,
            });
            return await response.json();
        }

        async function main() {
            const payments = Square.payments(appId, locationId);
            const card = await initializeCard(payments);

            const cardButton = document.getElementById('card-button');
            cardButton.addEventListener('click', async (event) => {
                event.preventDefault();
                const result = await card.tokenize();
                if (result.status === 'OK') {
                    await createPayment(result.token);
                    alert('Payment successful!');
                } else {
                    alert('Payment failed!');
                }
            });
        }

        main();
    </script>
</body>
</html>
