var LocalDataBase = {
	LATEST_STORED_ANSWER_COOKIE_NAME: 'LatestStoredAnswer',
	STORED_ANSWERS_KEY: 'Answers',

	available: function() {
		return (typeof(Storage) !== "undefined");
	},

	getLatestStoredAnswerId: function() {
		var cookieVal = Cookie.read(this.LATEST_STORED_ANSWER_COOKIE_NAME);
		return (cookieVal === null) ? 0 : parseInt(cookieVal);
	},

	setLatestStoredAnswerId: function(id) {
		Cookie.set(this.LATEST_STORED_ANSWER_COOKIE_NAME)
	},

	addQueryAnswer: function(answer) {
		this.addQueryAnswers([answer]);
	},

	addQueryAnswers: function(answers) {
		var stored = localStorage.getItem(this.STORED_ANSWERS_KEY);
		stored.pushElements(answers);
		localStorage.setItem(this.STORED_ANSWERS_KEY, stored);
	},

	getQueryAnswers: function() {
		return localStorage.getItem(this.STORED_ANSWERS_KEY);
	},

	removeAllAnswers: function() {
		Cookie.set(this.LATEST_STORED_ANSWER_COOKIE_NAME, '0', 0);
		localStorage.removeItem(this.STORED_ANSWERS_KEY);
	}
};