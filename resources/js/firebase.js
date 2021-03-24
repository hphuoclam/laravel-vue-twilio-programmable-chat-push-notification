import firebase from 'firebase/app'
import 'firebase/messaging'

// firebase init - add your own config here
const firebaseConfig = {
  apiKey: "",
  authDomain: "",
  projectId: "",
  storageBucket: "",
  messagingSenderId: "",
  appId: ""
};
if (firebase) {
  firebase.initializeApp(firebaseConfig);
}


export const handleFcmToken = chatClientInstance => {
  const messaging = firebase.messaging();

  if (firebase && messaging) {
    // requesting permission to use push notifications
    messaging.requestPermission().then(() => {
      // getting FCM token
      messaging.getToken().then((fcmToken) => {
        console.log(`fcm: ${fcmToken}`);
        chatClientInstance.setPushRegistrationId('fcm', fcmToken);

        // This is where we would handle the foreground.  This registers an event listener 
        // on new message from firebase for you to do something with it.
        // The chat window must have focus for messaging().onMessage to work.
        messaging.onMessage(payload => {
          console.log(`init - firebase.handleNotificationsForUser() - (foreground handler):  This push event has data: `, payload);
          chatClientInstance.handlePushNotification(payload);
          navigator.serviceWorker.getRegistrations().then(registration => {
            registration[0].showNotification(payload.data.author, {body: payload.data.twi_body});
          });

          // todo:  your implementatation for UI here
        });
      }).catch((err) => {
        // can't get token
        console.log("can't get token", err)
      });
    }).catch((err) => {
      // can't request permission or permission hasn't been granted to the web app by the user
      console.log("can't request permission or permission hasn't been granted to the web app by the user", err)
    });
  } else {
    // no Firebase library imported or Firebase library wasn't correctly initialized
    console.log("no Firebase library imported or Firebase library wasn't correctly initialized")
  }
}
