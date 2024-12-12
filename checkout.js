<script>

    var stripe = Stripe('pk_test_51QT2hl05mAP4XTAlBAgmESJZ8iSgRAFUh7A4LrKjfuCc1WU1PNnKCTLD2DrDqT5F6xCgIRQ7QE1JSba1roz0nWbF00zF9NWUwz');
    var elements = stripe.elements();
    var card = elements.create('card');
    card.mount('#card-element');

    card.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
        } else {
        displayError.textContent = '';
        }
    });

    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

    stripe.createPaymentMethod({
        type: 'card',
    card: card,
    billing_details: {
        name: form.querySelector('input[name=first_name]').value + ' ' + form.querySelector('input[name=last_name]').value,
    address: {
        line1: form.querySelector('input[name=address_line1]').value,
    line2: form.querySelector('input[name=address_line2]').value,
    city: form.querySelector('input[name=city]').value,
    state: form.querySelector('input[name=state]').value,
    postal_code: form.querySelector('input[name=zip]').value,
    country: form.querySelector('input[name=country]').value,
                },
            },
        }).then(function(result) {
            if (result.error) {
                var errorElement = document.getElementById('card-errors');
    errorElement.textContent = result.error.message;
            } else {
                // Ensure that the grandTotal is properly echoed from PHP as an integer value
                var amount = <?php echo (int)($grandTotal * 100); ?>;

    fetch('create_payment_intent.php', {
        method: 'POST',
    headers: {
        'Content-Type': 'application/json',
                    },
    body: JSON.stringify({
        payment_method_id: result.paymentMethod.id,
    amount: amount,
                    }),
                }).then(function(response) {
                    return response.json();
                }).then(function(paymentIntent) {
                    if (paymentIntent.error) {
                        var errorElement = document.getElementById('card-errors');
    errorElement.textContent = paymentIntent.error.message;
                    } else {
                        // Check the status of the PaymentIntent before confirming
                        if (paymentIntent.status === 'requires_confirmation') {
        stripe.confirmCardPayment(paymentIntent.client_secret, {
            payment_method: result.paymentMethod.id,
        }).then(function (result) {
            if (result.error) {
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
            } else {
                // After successful payment, proceed to place the order
                var paymentIntentId = paymentIntent.id;
                console.log('Payment Intent ID:', paymentIntentId); // Debugging statement

                fetch('place_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        first_name: form.querySelector('input[name=first_name]').value,
                        last_name: form.querySelector('input[name=last_name]').value,
                        address_line1: form.querySelector('input[name=address_line1]').value,
                        address_line2: form.querySelector('input[name=address_line2]').value,
                        city: form.querySelector('input[name=city]').value,
                        state: form.querySelector('input[name=state]').value,
                        zip: form.querySelector('input[name=zip]').value,
                        country: form.querySelector('input[name=country]').value,
                        payment_intent_id: paymentIntentId,
                    }),
                }).then(function (response) {
                    return response.json();
                }).then(function (data) {
                    if (data.success) {
                        // Redirect to success page after placing the order
                        window.location.href = 'payment_success.php?payment_intent=' + paymentIntentId;
                    } else {
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = data.error;
                    }
                }).catch(function (error) {
                    console.error('Order placement error:', error);
                });
            }
        });
                        } else {
                            // PaymentIntent is already confirmed, proceed to place the order
                            var paymentIntentId = paymentIntent.id;
    console.log('Payment Intent ID:', paymentIntentId); // Debugging statement

    fetch('place_order.php', {
        method: 'POST',
    headers: {
        'Content-Type': 'application/json',
                                },
    body: JSON.stringify({
        first_name: form.querySelector('input[name=first_name]').value,
    last_name: form.querySelector('input[name=last_name]').value,
    address_line1: form.querySelector('input[name=address_line1]').value,
    address_line2: form.querySelector('input[name=address_line2]').value,
    city: form.querySelector('input[name=city]').value,
    state: form.querySelector('input[name=state]').value,
    zip: form.querySelector('input[name=zip]').value,
    country: form.querySelector('input[name=country]').value,
    payment_intent_id: paymentIntentId,
                                }),
                            }).then(function(response) {
                                return response.json();
                            }).then(function(data) {
                                if (data.success) {
        // Redirect to success page after placing the order
        window.location.href = 'payment_success.php?payment_intent=' + paymentIntentId;
                                } else {
                                    var errorElement = document.getElementById('card-errors');
    errorElement.textContent = data.error;
                                }
                            }).catch(function(error) {
        console.error('Order placement error:', error);
                            });
                        }
                    }
                });
            }
        });
    });

</script>
    
</body >
</html >
