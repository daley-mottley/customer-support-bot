// Ensure that Axios is loaded
if (typeof axios === "undefined") {
  console.error("Axios is not loaded. Please make sure Axios is included.");
}

class Chatbot {
  constructor() {
    // Check if vacw_settings is defined to avoid undefined errors
    if (typeof vacw_settings === "undefined") {
      console.error("vacw_settings is not defined. Please check the plugin configuration.");
      return;
    }
    this.botAvatar = vacw_settings.avatar_url || "default-avatar.png";
    this.botGreeting =
      vacw_settings.bot_greeting || "Hi! How can I assist you today?";
    this.ajaxUrl = vacw_settings.ajax_url;
    this.securityNonce = vacw_settings.security;
    // Log the nonce to verify it's being set correctly
    console.log("Security nonce:", this.securityNonce);
    this.isRunning = false;
    this.setupEventListeners();
    this.greetingAppended = false;
  }

  setupEventListeners() {
    document.getElementById("message").addEventListener("keyup", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        this.sendMessage();
      }
    });

    document.getElementById("chatbot_toggle").onclick =
      this.toggleChatbot.bind(this);
    document.querySelector(".input-send").onclick = this.sendMessage.bind(this);
  }

  toggleChatbot() {
    const chatbot = document.getElementById("chatbot");
    const toggleButton = document.getElementById("chatbot_toggle");

    if (chatbot.classList.contains("collapsed")) {
      chatbot.classList.remove("collapsed");
      toggleButton.children[0].style.display = "none";
      toggleButton.children[1].style.display = "";
      if (!this.greetingAppended) {
        this.greetingAppended = true;
        setTimeout(
          () => this.appendMessage(this.botGreeting, "received"),
          1000
        );
      }
    } else {
      chatbot.classList.add("collapsed");
      toggleButton.children[0].style.display = "";
      toggleButton.children[1].style.display = "none";
    }
  }

  async sendMessage() {
    if (this.isRunning) return;

    const userMessage = document.getElementById("message").value.trim();
    if (!userMessage) return;

    this.isRunning = true;
    this.appendMessage(userMessage, "sent");
    this.appendLoader();

    try {
      // Create form data to match WordPress AJAX expectations
      const formData = new URLSearchParams();
      formData.append("action", "vacw_get_bot_response");
      formData.append("message", userMessage);
      formData.append("security", this.securityNonce);

      // Log the data being sent for debugging
      console.log("Sending data:", {
        action: "vacw_get_bot_response",
        message: userMessage,
        security: this.securityNonce,
      });

      const response = await axios.post(this.ajaxUrl, formData, {
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
      });

      this.removeLoader();

      if (response.data.success) {
        this.appendMessage(response.data.data.content, "received");
      } else {
        this.appendMessage(
          response.data.data || "Sorry, there was an error processing your request.",
          "received"
        );
      }
    } catch (error) {
      // Enhanced error logging to capture detailed response from server
      console.error(
        "Error details:",
        error.response ? error.response.data : error.message
      );
      this.removeLoader();
      const errorMessage =
        error.response?.data?.data ||
        "Sorry, there was an error processing your request.";
      this.appendMessage(errorMessage, "received");
    }

    this.isRunning = false;
    document.getElementById("message").value = "";
  }

  appendMessage(msg, type) {
    const messageBox = document.getElementById("message-box");
    const div = document.createElement("div");
    div.className = "chat-message-div";

    if (type === "received") {
      const avatarDiv = document.createElement("div");
      avatarDiv.className = "avatar";
      const avatarImg = document.createElement("img");
      avatarImg.src = this.botAvatar;
      avatarImg.alt = "Bot Avatar";
      avatarImg.className = "avatar-img";
      avatarDiv.appendChild(avatarImg);
      div.appendChild(avatarDiv);
    }

    const messageDiv = document.createElement("div");
    messageDiv.className = `chat-message-${type}`;
    messageDiv.textContent = msg;
    messageDiv.style.opacity = "0";
    div.appendChild(messageDiv);

    messageBox.appendChild(div);
    messageBox.scrollTop = messageBox.scrollHeight;

    setTimeout(() => {
      messageDiv.style.opacity = "1";
    }, 50);

    if (type === "sent") {
      document.getElementById("message").value = "";
    }
  }

  appendLoader() {
    const messageBox = document.getElementById("message-box");
    const div = document.createElement("div");
    div.className = "chat-message-div";

    const avatarDiv = document.createElement("div");
    avatarDiv.className = "avatar";
    const avatarImg = document.createElement("img");
    avatarImg.src = this.botAvatar;
    avatarImg.alt = "Bot Avatar";
    avatarImg.className = "avatar-img";
    avatarDiv.appendChild(avatarImg);

    const loaderMessageDiv = document.createElement("div");
    loaderMessageDiv.className = "chat-message-received";
    loaderMessageDiv.innerHTML = `
            <span class="loader">
                <span class="loader__dot"></span>
                <span class="loader__dot"></span>
                <span class="loader__dot"></span>
            </span>`;
    div.appendChild(avatarDiv);
    div.appendChild(loaderMessageDiv);

    messageBox.appendChild(div);
    messageBox.scrollTop = messageBox.scrollHeight;
  }

  removeLoader() {
    const loaderDiv = document.querySelector(".chat-message-received .loader");
    if (loaderDiv) {
      const parentDiv = loaderDiv.closest(".chat-message-div");
      parentDiv.remove();
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new Chatbot();
});
