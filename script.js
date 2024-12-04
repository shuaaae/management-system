const signUpButton = document.getElementById('signUpButton');
const signInButton = document.getElementById('signInButton');
const login = document.getElementById('login');
const register = document.getElementById('register');


signUpButton.addEventListener('click', function(){
    login.style.display="none";
    register.style.display="block";
})

signInButton.addEventListener('click', function(){
    login.style.display="block";
    register.style.display="none";
})
