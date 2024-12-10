const toggleButton = document.getElementById('toggle-btn')
const sidebar = document.getElementById('sidebar')
const id = document.getElementById('logo')

function toggleSidebar(){
  sidebar.classList.toggle('close')
  toggleButton.classList.toggle('rotate')
  
  closeAllSubMenus()
}

document.getElementById('uploadForm').addEventListener('submit', function(event) {
  event.preventDefault(); // Prevent page refresh
  
  var formData = new FormData(this); // Get form data
  
  // Send AJAX request
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'upload_profile_pic.php', true);
  
  // Handle the response from PHP
  xhr.onload = function() {
      if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          if (response.status === 'success') {
              // Update the profile image on the page without refreshing
              document.getElementById('profilePic').src = '../uploads/profile_pics/' + response.newPic;
              alert('Profile picture updated successfully!');
          } else {
              alert('Failed to upload image.');
          }
      }
  };
  
  xhr.send(formData); // Send the form data
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
