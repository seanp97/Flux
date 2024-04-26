class HttpRequest {
    
    GetJSON = async (url) => {
        this._data = await fetch(url);
        this._json = await this._data.json();
        return this._json;
    }
    
    PostJSON = async (url, data) => {
        try {
            this.response = await fetch(url, {
                method: "POST",
                mode: 'no-cors',
                headers: {'Content-Type': 'application/json'}, 
                body: JSON.stringify(data)
            });
    
            if (!this.response.ok) {
                throw new Error('Network response was not ok');
            }
    
            this.responseData = await this.response.json();
            console.log(this.responseData);
        } catch (error) {
            console.error('Error:', error);
        }
    };
    

    PostForm(path, params, method='POST') {
        this.form = document.createElement('form');
        this.form.method = method;
        this.form.action = path;
      
        for (const key in params) {
          if (params.hasOwnProperty(key)) {
            this.hiddenField = document.createElement('input');
            this.hiddenField.type = 'hidden';
            this.hiddenField.name = key;
            this.hiddenField.value = params[key];
      
            this.form.appendChild(this.hiddenField);
          }
        }
      
        document.body.appendChild(this.form);

        this.form.addEventListener('submit', function(event) {
            event.preventDefault();
        });

        this.form.submit();
    }
}