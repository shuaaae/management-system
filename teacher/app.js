const toggleButton = document.getElementById('toggle-btn')
const sidebar = document.getElementById('sidebar')
const id = document.getElementById('logo')

function toggleSidebar(){
  sidebar.classList.toggle('close')
  toggleButton.classList.toggle('rotate')
  
  closeAllSubMenus()
}

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
