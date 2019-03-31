const promise_call = (option) => {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest();
      xhr.open(option.method, option.url);
      xhr.setRequestHeader('Content-type','application/json; charset=utf-8');
      xhr.onload = () => resolve(JSON.parse(xhr.responseText));
      xhr.onerror = () => reject(JSON.parse(xhr.statusText));
      xhr.send(JSON.stringify(option.data));
    });
  };