const fpPromise = import('./fp_lib.js')
    .then(FingerprintJS => FingerprintJS.load())

fpPromise
    .then(fp => fp.get())
        .then(result => {
            visitorId = result.visitorId;

            const visitorIdinput = document.getElementById('visitorId');
            visitorIdinput.value = visitorId;
        })