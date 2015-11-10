import jsonschema from 'jsonschema'
import schemafile from './../schema.json!json'

export default class {






	// class constructor
	constructor() {

		// define some class defaults
		this.savephplocation = "/api.php"
		this.saveslocation = "/saves"

		// load schema validator
		this.validator = new jsonschema.Validator();
		this.schema = schemafile;

	}








	// private helpers
	checkStatus(response) {
		if (response.status >= 200 && response.status < 300) {
			return response;
		} else {
			throw new Error(response.status+' '+response.statusText);
		}
	}
	parseJSON(response) {
		return response.json()
		.then(function(json){
			return json;
		})
		.catch(function(error){
			error.message = 'JSON does not decode ('+error.message+') in response from '+response.url;
			throw error;
		});
	}
	checkResult(json) {
		if(json.error) throw new Error(json.error);
		else return json.result;
	}

	// validate schema wrapper
	validateJSON(jsondata) {
		return this.validator.validate(jsondata, this.schema);
	}









	// public methods
	saveState(jsondata) {

		let instance = this;

		//wrap in promise (and return)
		return new Promise(function(resolve,reject){

			// json schema validate
			let ValidatorResult = instance.validateJSON(jsondata);
			if (!ValidatorResult.valid) {

				var error = new Error('JSON does not validate to schema (clientside):');
				for(let validatorError of ValidatorResult.errors){
					error.message += ' '+validatorError.message+';';
				}
				throw error;

			} else {

				// POST to server
				return fetch('api.php', {
					method: 'post',
					headers: {
						'Accept': 'application/json',
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(jsondata)
				})
				.then(instance.checkStatus)
				.then(instance.parseJSON)
				.then(instance.checkResult)
				.then(function(data){
					resolve(data);
				})
				.catch(function(error){
					reject(error);
				});
			}

		});
		
	}




	loadState(token) {

		let instance = this;

		return fetch(instance.saveslocation+'/'+token+'.json')
		//.then(instance.checkStatus, function(err){throw err;})
		.then(instance.checkStatus)
		.then(instance.parseJSON)
		.then(function(obj) {
			return obj;
		})
		.catch(function(error) {
			console.log('loadJSON final error',error);
			throw error;
		});

	}

}