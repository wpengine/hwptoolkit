const useLocalStorage = {
	getItem: (key) => {
		if (typeof window !== "undefined") {
			return localStorage.getItem(key);
		}
	},
	setItem: (key, value) => {
		if (typeof window !== "undefined") {
			localStorage.setItem(key, value);
		}
	},
	removeItem: (key) => {
		if (typeof window !== "undefined") {
			localStorage.removeItem(key);
		}
	},
	saveToLocalStorage: (key, data) => {
		if (typeof window !== "undefined") {
			localStorage.setItem(key, JSON.stringify(data));
		}
	},
	getFromLocalStorage: ((key) => {
		if (typeof window !== "undefined") {
			const data = localStorage.getItem(key);
			if (data) {
				try {
					return JSON.parse(data);
	            } catch (error) {
	                console.error(`Error parsing ${key} from localStorage:`, error);
	                localStorage.removeItem(key);
	            }
	        }
	    }
	    return null;
	}),
};

export default useLocalStorage;
