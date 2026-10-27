/**
 * SOBHA REALTY LUXURY CRM - ADMIN SCRIPT
 */
function updateLeadStatus(leadId, newStatus) {
  const formData = new FormData();
  formData.append('id', leadId);
  formData.append('status', newStatus);

  fetch('update-status.php', {
    method: 'POST',
    body: formData
  })
  .then(function (res) { return res.json(); })
  .then(function (data) {
    if (!data.success) {
      alert('Failed to update lead status: ' + (data.message || 'Error occurred'));
    }
  })
  .catch(function (err) {
    console.error('Error updating status:', err);
    alert('Communication error updating status');
  });
}
