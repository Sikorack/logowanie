async function createRegistration() {
  try {
    const userName = document.querySelector("#userName").value;
    if (
      !window.fetch ||
      !navigator.credentials ||
      !navigator.credentials.create
    ) {
      throw new Error("Przeglądarka nieobsługiwana.");
    }
    let rep = await fetch(
      "../../klucz/server_register.php?userName=" + encodeURIComponent(userName),
      { method: "GET", cache: "no-cache" }
    );
    const createArgs = await rep.json();

    if (createArgs.success === false) {
      throw new Error(createArgs.msg || "wystąpił nieznany błąd");
    }

    recursiveBase64StrToArrayBuffer(createArgs);

    const cred = await navigator.credentials.create(createArgs);

    const AttestationResponse = {
      clientDataJSON: cred.response.clientDataJSON
        ? arrayBufferToBase64(cred.response.clientDataJSON)
        : null,
      attestationObject: cred.response.attestationObject
        ? arrayBufferToBase64(cred.response.attestationObject)
        : null,
    };

    rep = await fetch("../../klucz/server_register.php", {
      method: "POST",
      body: JSON.stringify(AttestationResponse),
      cache: "no-cache",
    });

    const ServerResponse = await rep.json();

    if (ServerResponse.success) {
      window.alert(ServerResponse.msg || "Poprawnie dodano nowy klucz YubiKey");
      window.location.replace("../logowanie/logowanie.php");
    } else {
      throw new Error(ServerResponse.msg);
    }
  } catch (err) {
    window.alert(err.message || "wystąpił nieznany błąd");
  }
}

async function checkRegistration() {
  try {
    const userName = document.querySelector("#userName").value;
    if (!window.fetch || !navigator.credentials || !navigator.credentials.get) {
      throw new Error("Przeglądarka nieobsługiwana.");
    }
    let rep = await fetch(
      "../../klucz/server_login.php?userName=" + encodeURIComponent(userName),
      { method: "GET", cache: "no-cache" }
    );
    const getArgs = await rep.json();
    if (getArgs.success === false) {
      throw new Error(getArgs.msg);
    }

    recursiveBase64StrToArrayBuffer(getArgs);

    const cred = await navigator.credentials.get(getArgs);

    const AttestationResponse = {
      id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
      clientDataJSON: cred.response.clientDataJSON
        ? arrayBufferToBase64(cred.response.clientDataJSON)
        : null,
      authenticatorData: cred.response.authenticatorData
        ? arrayBufferToBase64(cred.response.authenticatorData)
        : null,
      signature: cred.response.signature
        ? arrayBufferToBase64(cred.response.signature)
        : null,
    };

    rep = await window.fetch("../../klucz/server_login.php", {
      method: "POST",
      body: JSON.stringify(AttestationResponse),
      cache: "no-cache",
    });
    const ServerResponse = await rep.json();

    if (ServerResponse.success) {
      window.location.replace("../../index.php");
    } else {
      throw new Error(ServerResponse.msg);
    }
  } catch (err) {
    window.alert(err.message || "wystąpił nieznany błąd");
  }
}

// window.onload = function () {
//   if (!window.isSecureContext && location.protocol !== "https:") {
//     location.href = location.href.replace("http://", "https://");
//   }
// };

function recursiveBase64StrToArrayBuffer(obj) {
  let prefix = "=?BINARY?B?";
  let suffix = "?=";
  if (typeof obj === "object") {
    for (let key in obj) {
      if (typeof obj[key] === "string") {
        let str = obj[key];
        if (
          str.substring(0, prefix.length) === prefix &&
          str.substring(str.length - suffix.length) === suffix
        ) {
          str = str.substring(prefix.length, str.length - suffix.length);

          let binary_string = window.atob(str);
          let len = binary_string.length;
          let bytes = new Uint8Array(len);
          for (let i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
          }
          obj[key] = bytes.buffer;
        }
      } else {
        recursiveBase64StrToArrayBuffer(obj[key]);
      }
    }
  }
}

function arrayBufferToBase64(buffer) {
  let binary = "";
  let bytes = new Uint8Array(buffer);
  let len = bytes.byteLength;
  for (let i = 0; i < len; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return window.btoa(binary);
}

async function zmienNazwe(credentialId) {
  try {
    let nowaNazwa = await window.prompt("Wprowadź nową nazwę klucza:", "");
    nowaNazwa = nowaNazwa.trim();
    if (nowaNazwa == null || nowaNazwa == "" || nowaNazwa == undefined)
      throw new Error("Nie wprowadziłeś nazwy");
    const infoKlucza = {
      credentialId: credentialId,
      nowaNazwa: nowaNazwa,
    };
    const odpowiedz = await window.fetch("../../klucz/edit.php", {
      method: "POST",
      body: JSON.stringify(infoKlucza),
      cache: "no-cache",
    });
    const odpowiedzSerwera = await odpowiedz.json();
    if (odpowiedzSerwera.success) {
      window.location.replace("prywatnosc.php");
    }
  } catch {
    window.alert("Wystąpił błąd podczas zmiany nazwy");
  }
}

async function usunKlucz(credentialId) {
  try {
    const infoKlucza = {
      credentialId: credentialId,
    };
    const odpowiedz = await window.fetch("../../klucz/delete.php", {
      method: "POST",
      body: JSON.stringify(infoKlucza),
      cache: "no-cache",
    });
    const odpowiedzSerwera = await odpowiedz.json();
    if (odpowiedzSerwera.success) {
      alert("Poprawnie usunięto klucz. Zaloguj się ponownie.");
      window.location.replace("../logowanie/wylogowanie.php");
    }
  } catch {
    window.alert("Wystąpił błąd podczas usuwania klucza");
  }
}
