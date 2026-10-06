/* support.html-la irukkura "4. FORM SUBMIT" section-a idha vachu replace pannunga.
   Mudhalla oru line add pannunga (ELEMENT REFERENCES section-la):
   const order = $('custOrder');
*/

const submitBtn = form.querySelector('button[type=submit]');

form.onsubmit = async (e) => {
  e.preventDefault();
  let ok = true;

  if (name.value.trim().length < 2) { setInvalid('f-name', true); ok = false; }
  else setInvalid('f-name', false);

  if (!/^[6-9]\d{9}$/.test(mobile.value)) { setInvalid('f-mobile', true); ok = false; }
  else setInvalid('f-mobile', false);

  if (msg.value.trim().length < 5) { setInvalid('f-msg', true); ok = false; }
  else setInvalid('f-msg', false);

  if (!ok) return;

  submitBtn.disabled = true;
  try {
    const res = await fetch('save_ticket.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value.trim(),
        mobile: mobile.value,
        order_id: order.value.trim(),
        message: msg.value.trim()
      })
    });
    const result = await res.json();
    if (!result.ok) throw new Error(result.error);

    successMsg.textContent = `Thanks ${name.value.trim()}, ticket #${result.ticket_id} created. Our team will reach out on ${mobile.value} shortly.`;
    form.classList.add('hide');
    successCard.classList.add('show');
  } catch (err) {
    alert('Could not send your message — please try again.');
    submitBtn.disabled = false;
  }
};
