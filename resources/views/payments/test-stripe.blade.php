
<input id="card-holder-name" type="text">

<!-- Stripe Elements Placeholder -->
<div id="card-element"></div>

<button id="card-button">
    Process Payment
</button>

<script src="https://js.stripe.com/v3/"></script>

<script>
    const stripe = Stripe('{{ setting('payment.stripe_test_public_key') }}');

    const elements = stripe.elements();
    const cardElement = elements.create('card');

    cardElement.mount('#card-element');


    const cardHolderName = document.getElementById('card-holder-name');
	const cardButton = document.getElementById('card-button');

	cardButton.addEventListener('click', async (e) => {
	    const { paymentMethod, error } = await stripe.createPaymentMethod(
	        'card', cardElement, {
	            billing_details: { name: cardHolderName.value }
	        }
	    );

	    if (error) {
	    	console.log(error.message);
	        // Display "error.message" to the user...
	    } else {
	    	url = 'http://127.0.0.1:8000/admin/payment/gateway/stripe/test/single-pay/'+paymentMethod.id;
            window.location.href=url;

	    	console.log(paymentMethod);
	    	alert('win');
	        // The card has been verified successfully...
	    }
	});

</script>