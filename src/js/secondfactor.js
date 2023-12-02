/* (license-header)
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */
function verify() {
  if (!document.getElementById("code").checkValidity()) {
    document.querySelector(".mdl-js-snackbar").MaterialSnackbar.showSnackbar({
      message: "El código de verificación debe tener 6 cifras."
    });

    return;
  }

  var body = {
    code: document.getElementById("code").value
  };

  var content = document.getElementById("content");
  content.innerHTML = '<div class="mdl-spinner mdl-js-spinner is-active"></div>';
  content.style.textAlign = "center";
  componentHandler.upgradeElements(content);

  fetch("ajax/verifysecuritycode.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify(body)
  }).then(response => {
    if (response.status !== 200) {
      throw new Error("HTTP status is not 200.");
    }

    return response.json();
  }).then(response => {
    switch (response.status) {
      case "ok":
      document.location = "index.php";
      break;

      case "wrongCode":
      document.location = "index.php?msg=secondfactorwrongcode";
      break;

      default:
      console.error("An unknown status code was returned.");
    }
  }).catch(err => console.error("An unexpected error occurred.", err));
}

function verifyKeypress(e) {
  if (event.keyCode == 13) {
    verify();
  }
}

function startWebauthn() {
  fetch("ajax/startwebauthnauthentication.php", {
    method: "POST"
  }).then(response => {
    if (response.status !== 200) {
      response.text(); // @TODO: Remove this. It is only used so the response is available in Chrome Dev Tools
      throw new Error("HTTP status is not 200.");
    }

    return response.json();
  }).then(response => {
    recursiveBase64StrToArrayBuffer(response);
    return response;
  }).then(getCredentialArgs => {
    return navigator.credentials.get(getCredentialArgs);
  }).then(cred => {
    return {
      id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
      clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
      authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
      signature : cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null
    };
  }).then(JSON.stringify).then(AuthenticatorAttestationResponse => {
    return window.fetch("ajax/completewebauthnauthentication.php", {
      method: "POST",
      body: AuthenticatorAttestationResponse,
    });
  }).then(response => {
    if (response.status !== 200) {
      response.text(); // @TODO: remove this. It is only used so the response is available in Chrome Dev Tools
      throw new Error("HTTP status is not 200 (2).");
    }

    return response.json();
  }).then(json => {
    if (json.status == "ok") {
      document.location = "index.php";
    }
  }).catch(err => console.error("An unexpected error occurred.", err));
}

window.addEventListener("load", function() {
  if (document.getElementById("totp")) {
    document.getElementById("verify").addEventListener("click", verify);
    document.getElementById("code").addEventListener("keypress", verifyKeypress);
    document.getElementById("code").focus();
    document.querySelector("a[href=\"#totp\"]").addEventListener("click", _ => {
      document.getElementById("code").focus();
    });
  }

  if (document.getElementById("startwebauthn")) {
    document.getElementById("startwebauthn").addEventListener("click", startWebauthn);
  }
});
