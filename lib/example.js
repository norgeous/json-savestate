import stateapi from './api'

export default class {

	constructor() {

		let instance = this;
		this.savestateapi = new stateapi();

		this.savestateapi.loadState('d1d78ee0')
		.then(function(data){
			instance.state = data;


			var btn = document.createElement("BUTTON");        // Create a <button> element
			var t = document.createTextNode("CLICK ME");       // Create a text node
			btn.appendChild(t);                                // Append the text to <button>
			btn.addEventListener("click", function(){instance.saveButton();});
			document.body.appendChild(btn);                    // Append <button> to <body>

		})
		.catch(function(error) {
			console.log('FINAL', error);
		});

	}

	saveButton(){

		this.savestateapi.saveState(this.state)
		.then(function(hash) {
			console.log('saved as hash "'+hash+'"');
		})
		.catch(function(error) {
			console.log('FINAL', error);
		});
	}

} 
