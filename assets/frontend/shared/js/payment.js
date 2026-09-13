(function () {
  var tabs = document.querySelectorAll('[data-pay-tab]');
  var panels = document.querySelectorAll('[data-pay-panel]');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var id = tab.getAttribute('data-pay-tab');
      tabs.forEach(function (t) {
        t.classList.toggle('is-active', t === tab);
        t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
      });
      panels.forEach(function (p) {
        p.classList.toggle('is-active', p.getAttribute('data-pay-panel') === id);
      });
    });
  });

  var number = document.getElementById('cardNumber');
  var expiry = document.getElementById('cardExpiry');
  var cvv = document.getElementById('cardCvv');
  var name = document.getElementById('cardName');
  var form = document.getElementById('ecCardForm');
  var payload = document.getElementById('cardPayload');
  var btn = document.getElementById('cardPayBtn');
  var visualNum = document.querySelector('.ec-pay-card-num');

  if (number) {
    number.addEventListener('input', function () {
      var digits = number.value.replace(/\D+/g, '').slice(0, 19);
      number.value = digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
      if (visualNum) {
        visualNum.textContent = number.value || '•••• •••• •••• ••••';
      }
    });
  }
  if (expiry) {
    expiry.addEventListener('input', function () {
      var v = expiry.value.replace(/\D+/g, '').slice(0, 4);
      if (v.length >= 3) v = v.slice(0, 2) + ' / ' + v.slice(2);
      expiry.value = v;
    });
  }
  if (cvv) {
    cvv.addEventListener('input', function () {
      cvv.value = cvv.value.replace(/\D+/g, '').slice(0, 4);
    });
  }

  function b64ToBuf(b64) {
    var bin = atob(b64);
    var bytes = new Uint8Array(bin.length);
    for (var i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
    return bytes.buffer;
  }

  function bufToB64(buf) {
    var bytes = new Uint8Array(buf);
    var str = '';
    for (var i = 0; i < bytes.length; i++) str += String.fromCharCode(bytes[i]);
    return btoa(str);
  }

  async function encryptCard(data) {
    if (!window.EC_PAYMENT || !window.EC_PAYMENT.publicKey) {
      throw new Error('Payment encryption key is missing.');
    }
    if (!window.crypto || !window.crypto.subtle) {
      throw new Error('Secure browser crypto is required for card payments.');
    }
    var key = await crypto.subtle.importKey(
      'spki',
      b64ToBuf(window.EC_PAYMENT.publicKey),
      { name: 'RSA-OAEP', hash: 'SHA-1' },
      false,
      ['encrypt']
    );
    var encoded = new TextEncoder().encode(JSON.stringify(data));
    var cipher = await crypto.subtle.encrypt({ name: 'RSA-OAEP' }, key, encoded);
    return bufToB64(cipher);
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!number || !expiry || !cvv || !name || !payload) return;

      var digits = number.value.replace(/\D+/g, '');
      var exp = expiry.value.replace(/\D+/g, '');
      if (digits.length < 13 || digits.length > 19) {
        alert('Enter a valid card number.');
        return;
      }
      if (exp.length !== 4) {
        alert('Enter expiry as MM/YY.');
        return;
      }
      if (cvv.value.length < 3) {
        alert('Enter a valid CVV.');
        return;
      }

      var month = parseInt(exp.slice(0, 2), 10);
      var year = 2000 + parseInt(exp.slice(2), 10);
      if (month < 1 || month > 12) {
        alert('Invalid expiry month.');
        return;
      }

      if (btn) {
        btn.disabled = true;
        btn.textContent = 'Encrypting & paying…';
      }

      encryptCard({
        number: digits,
        exp_month: month,
        exp_year: year,
        cvv: cvv.value,
        name: name.value.trim()
      }).then(function (cipher) {
        payload.value = cipher;
        number.value = '';
        expiry.value = '';
        cvv.value = '';
        form.submit();
      }).catch(function (err) {
        alert(err.message || 'Unable to encrypt card details.');
        if (btn) {
          btn.disabled = false;
          btn.textContent = 'Pay again';
        }
      });
    });
  }
})();
