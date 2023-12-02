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
window.addEventListener("load", function() {
  document.querySelector(".addsecuritykey").addEventListener("click", function() {
    document.querySelector("#addsecuritykey").showModal();
    /* Or dialog.show(); to show the dialog without a backdrop. */
  });

  document.getElementById("registersecuritykey").addEventListener("click", e => {
    e.preventDefault();

    if (document.getElementById("addsecuritykeyform").reportValidity()) {
      fetch("ajax/addsecuritykey.php", {
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
      }).then(createCredentialArgs => {
        return navigator.credentials.create(createCredentialArgs);
      }).then(cred => {
        return {
            clientDataJSON: cred.response.clientDataJSON  ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
            attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null,
            name: document.getElementById("name").value
        };
      }).then(JSON.stringify).then(AuthenticatorAttestationResponse => {
        return window.fetch("ajax/addsecuritykey2.php", {
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
          document.location = "securitykeys.php?msg=securitykeyadded";
        }
      }).catch(err => console.error("An unexpected error occurred.", err));
    }
  });
});
