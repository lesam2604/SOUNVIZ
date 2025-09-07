(async function () {
  try {
    await (localStorage.getItem('token') ? getUser() : Promise.reject(null));
    location.replace(`/dashboard`);
  } catch (error) {
    if (error) {
      console.log(error);
      localStorage.clear();
      deleteCookie('user-type');
    }

    $(async () => {
      try {
        await (typeof render === 'function' ? render() : Promise.resolve());
        hidePreloader();
      } catch ({ error }) {
        console.log(error);
      }
    });
  }
})();
