import * as firebase from './assets/admin/js/firebase-app.js';
var config = {
	apiKey: "%APIKEY%",
	authDomain: "%AUTHDOMAIN%",
	databaseURL: "%DATABASEURL%",
	projectId: "%PROJECTID%",
	storageBucket: "%STRORAGEBUCKET%",
	messagingSenderId: "%MESSAGINGSENDERID%",
	appId: "%APPID%"
};

firebase.initializeApp(config);
var notification = [];
var icon = '';