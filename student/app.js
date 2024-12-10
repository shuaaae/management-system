const toggleButton = document.getElementById('toggle-btn')
const sidebar = document.getElementById('sidebar')
const id = document.getElementById('logo')

function toggleSidebar(){
  sidebar.classList.toggle('close')
  toggleButton.classList.toggle('rotate')
  
  closeAllSubMenus()
}

document.querySelector('input[type="file"]').addEventListener('change', function (event) {
  var formData = new FormData();
  formData.append('profile_pic', event.target.files[0]);

  fetch('', { // Send request to the current page
      method: 'POST',
      body: formData
  })
  .then(response => response.json())
  .then(data => {
      if (data.status === 'success') {
          // Refresh the page to display the updated image
          window.location.reload();
      } else {
          alert('Error uploading profile picture. Please try again.');
      }
  })
  .catch(error => console.error('Error:', error));
});


function toggleSubMenu(button){

  if(!button.nextElementSibling.classList.contains('show')){
    closeAllSubMenus()
  }

  button.nextElementSibling.classList.toggle('show')
  button.classList.toggle('rotate')

  if(sidebar.classList.contains('close')){
    sidebar.classList.toggle('close')
    toggleButton.classList.toggle('rotate')
  }
}

function closeAllSubMenus(){
  Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
    ul.classList.remove('show')
    ul.previousElementSibling.classList.remove('rotate')
  })
}
// JavaScript function to toggle logo visibility
function toggleLogo() {
  const logoContainer = document.getElementById('logo-container');
  
  // Check the current state and toggle visibility
  if (logoContainer.style.visibility === 'hidden') {
    logoContainer.style.visibility = 'visible';
    logoContainer.style.opacity = '1';
  } else {
    logoContainer.style.visibility = 'hidden';
    logoContainer.style.opacity = '0';
  }
}

// Attach the toggleLogo function to the sidebar toggle button
document.getElementById('toggle-btn').addEventListener('click', toggleLogo);
