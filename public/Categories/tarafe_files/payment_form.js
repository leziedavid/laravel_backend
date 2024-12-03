
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }

function deleteCookie(cname){
    document.cookie = cname+"=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
}

function create_submit_form() {

    let div = document.createElement("div");

    // On recupere les informations à transmettre
    var numero_commande = getCookie('txnid');
    var target = getCookie('target');
    var agence_code = getCookie('agency');
    var hash = getCookie('hash');
    var domain_name = getCookie('domain_name');
    var montant = getCookie('amount');
    var url_notif_success = getCookie('success_url');
    var url_notif_failed = getCookie('cancel_url');
    var clientPhone = getCookie('bill_phone');
    var clientLastname = getCookie('bill_last_name');
    var clientFirstname = getCookie('bill_first_name');
    var email = getCookie('bill_email');
    var ville = getCookie('bill_city');

    //remove cookie
    deleteCookie("txnid");
    div.innerHTML = `
	<button id="modalButton" style="display:none;">Open Modal</button>
	<div id="myModal" class="modal">
	  <!-- Modal content -->
	  <div class="modal-content">
	    <div class="modal-header">
	      <span class="close">&times;</span>
	      <h2>Commande réussie.</h2>
	    </div>
	    <div class="modal-body">
	      <p>Félicitations! Votre commande a été validée avec succcés. Merci de cliquer sur le bouton pour procéder au paiement.</p>
	      <p style="margin-top:20px; margin-bottom: 20px;">
	      	<center>
			<form method="post" id="payment-form" action="${ target }" target="TouchPay">
			    <input type="hidden" name="order_number" value="${ numero_commande }">
			    <input type="hidden" name="agency_code" value="${ agence_code }">
			    <input type="hidden" name="hash" value="${ hash }">
			    <input type="hidden" name="domain_name" value="${ domain_name }">
			    <input type="hidden" name="amount" value="${ montant }">
			    <input type="hidden" name="url_notif_success" value="${ url_notif_success }">
			    <input type="hidden" name="url_notif_failed" value="${ url_notif_failed }">
			    <input type="hidden" name="email" value="${ email }">
			    <input type="hidden" name="clientFirstname" value="${ clientFirstname }">
			    <input type="hidden" name="clientLastname" value="${ clientLastname }">
			    <input type="hidden" name="clientPhone" value="${ clientPhone }">
			    <input id="submit-btn" type="submit" value="Procéder au paiement" />
			</form>
		</center>
	      </p>
	    </div>
	    <div class="modal-footer">
	      <h6><span><img src="https://intouchgroup.net/wp-content/themes/intouch-clarity/assets/img/LOGO_TOUCH_BLEU.svg" alt="INTOUCH LOGO" width="19"></span><span>Intouch SA.</span></h6>
	    </div>
	  </div>
	</div>
    <style>
		.modal {
		  font-family: Arial, Helvetica, sans-serif;
		  display: none;
		  position: fixed;
		  z-index: 1; 
		  padding-top: 100px;
		  left: 0;
		  top: 0;
		  width: 100%; 
		  height: 100%;
		  overflow: auto;
		  background-color: rgb(0,0,0); 
		  background-color: rgba(0,0,0,0.4);
		}

		.modal-content {
		  position: relative;
		  background-color: #fefefe;
		  margin: auto;
		  padding: 0;
		  border: 1px solid #888;
		  width: 80%;
		  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
		  -webkit-animation-name: animatetop;
		  -webkit-animation-duration: 0.4s;
		  animation-name: animatetop;
		  animation-duration: 0.4s
		}

		@-webkit-keyframes animatetop {
		  from {top:-300px; opacity:0} 
		  to {top:0; opacity:1}
		}

		@keyframes animatetop {
		  from {top:-300px; opacity:0}
		  to {top:0; opacity:1}
		}

		.close {
		  color: white;
		  float: right;
		  font-size: 28px;
		  font-weight: bold;
		}

		.close:hover,
		.close:focus {
		  color: #000;
		  text-decoration: none;
		  cursor: pointer;
		}

		.modal-header {
		  padding: 2px 16px;
		  background-color: #59bfc9;
		  color: white;
		}

		.modal-body {padding: 2px 16px;}

		.modal-footer {
		  padding: 2px 16px;
		  background-color: #59bfc9;
		  color: white;
		}
		</style>
    `;

    document.body.appendChild(div);
    //let submit_btn = document.getElementById('submit-btn');
    // submit_btn.click();
    
	// Get the modal
	var modal = document.getElementById("myModal");

	// Get the button that opens the modal
	var btn = document.getElementById("modalButton");

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName("close")[0];

	// When the user clicks the button, open the modal 
	btn.onclick = function() {
	  modal.style.display = "block";
	}

	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
	  modal.style.display = "none";
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	  if (event.target == modal) {
	    modal.style.display = "none";
	  }
	}
	btn.click();
}
