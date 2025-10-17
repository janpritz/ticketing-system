self.addEventListener("push", (event) => {
    const notification = event.data.json();

    event.waitUntil(
        self.registration.showNotification(notification.title, {
            body: notification.body,
            icon: "public/logo.png",//working logo path
            data: {
                url: notification.url
            }
        })
    )
});


self.addEventListener("notificationclick", (event) => {
    event.waitUntil(
        clients.openWindow(event.notification.data.url)
    )
})