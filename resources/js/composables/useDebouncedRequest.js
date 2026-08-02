import { onBeforeUnmount } from 'vue';

export const useDebouncedRequest = (delay = 400) => {
  let timer = null;
  let controller = null;

  const cancel = () => {
    if (timer) {
      clearTimeout(timer);
      timer = null;
    }

    if (controller) {
      controller.abort();
      controller = null;
    }
  };

  const run = (callback) => {
    cancel();

    return new Promise((resolve, reject) => {
      timer = setTimeout(async () => {
        controller = new AbortController();

        try {
          resolve(await callback(controller.signal));
        } catch (error) {
          if (error.name !== 'CanceledError' && error.code !== 'ERR_CANCELED') {
            reject(error);
          }
        } finally {
          controller = null;
          timer = null;
        }
      }, delay);
    });
  };

  onBeforeUnmount(cancel);

  return { run, cancel };
};
