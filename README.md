<h1 align="center">

<span align="center">
       <img src="https://github.com/daley-mottley/customer-support-bot/blob/main/assets/images/logo-white.png" alt="logo" height="150" width="150" />
</span>
<br>
☀️ Customer Support Bot ☀️

</h1>

<p align="center">
<a href="https://github.com/daley-mottley/customer-support-bot/issues/new?assignees=&labels=enhancement&projects=&template=feature_request.yml&title=%5BFeature+Request%5D+">Request Feature</a>
     ·
    <a href="https://shorturl.at/AZvWp" target="blank">View Demo </a> 
    ·
    <a href="https://github.com/daley-mottley/customer-support-bot/issues/new?assignees=&labels=bug&projects=&template=bug_report.yml&title=%5BBug%5D+">Report Bug</a>

</p>

<p align="center"><a href="https://github.com/daley-mottley/customer-support-bot/blob/main/CONTRIBUTING.md">Contributing Guidelines & Code of Conduct</a></p>

<div align="center">
<p>

[![Open Source Love svg1](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)](https://github.com/ellerbrock/open-source-badges/)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat)
![GitHub forks](https://img.shields.io/github/forks/daley-mottley/customer-support-bot)
![GitHub Repo stars](https://img.shields.io/github/stars/daley-mottley/customer-support-bot)
![GitHub contributors](https://img.shields.io/github/contributors/daley-mottley/customer-support-bot)
![GitHub last commit](https://img.shields.io/github/last-commit/daley-mottley/customer-support-bot)
![GitHub repo size](https://img.shields.io/github/repo-size/daley-mottley/customer-support-bot)
![GitHub total lines](https://sloc.xyz/github/daley-mottley/customer-support-bot)
![Github](https://img.shields.io/github/license/daley-mottley/customer-support-bot)
![GitHub issues](https://img.shields.io/github/issues/daley-mottley/customer-support-bot)
![GitHub closed issues](https://img.shields.io/github/issues-closed-raw/daley-mottley/customer-support-bot)
![GitHub pull requests](https://img.shields.io/github/issues-pr/daley-mottley/customer-support-bot)
![GitHub closed pull requests](https://img.shields.io/github/issues-pr-closed/daley-mottley/customer-support-bot)
</p>
</div>

 <hr>

![Customer Support Bot Screenshot](https://res.cloudinary.com/dzpafdvkm/image/upload/v1726858049/Portfolio/customer-support-bot-screenshot.png)


## Project Overview 📑

**Customer Support Bot** is a WordPress plugin that enables businesses to automate customer support using AI technology, now powered by the **Google Gemini API**. The bot offers features like knowledge base searches (basic conversational ability), appointment scheduling checks, and appointment booking capabilities to enhance user experience and reduce the workload on customer support agents.

## Table of Contents📋

- [Features](#features-)
- [Technologies Used](#technologies-used-)
- [Installation](#installation-️)
  - [Download from GitHub](#1-download-from-github)
  - [Clone the Repository](#2-clone-the-repository)
- [Configuration](#configuration-) 
- [Help Wanted](#help-wanted-)
- [Report A Bug](#report-a-bug-)
- [Contributions](#contributions)
- [Contributors](#contributors)
- [Let's Stay Connected](#lets-stay-connected)

## Features 📝
<ul style="list-style-type:none;padding-left:0;">
<li>📌 <strong>Conversational AI:</strong> Engage users with responses powered by Google Gemini.</li>
<li>📌 <strong>Appointment Availability Checks:</strong> Check appointment slots via webhook integration.</li>
<li>📌 <strong>Appointment Scheduling:</strong> Allow users to book appointments through the bot via webhook integration.</li>
<li>📌 <strong>Customization Settings:</strong> Customize bot appearance (colors), avatar, name, and greeting in the WordPress admin panel.</li>
</ul>

## Technologies Used 📚

- [WordPress](https://wordpress.org/) – CMS platform where the plugin is installed and activated.
- [PHP](https://www.php.net/) – Server-side language for API interactions and WordPress plugin development.
- [JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript) – Frontend logic for chatbot interaction.
- [Axios](https://axios-http.com/docs/intro) – HTTP client used on the frontend for AJAX requests.
- [Bootstrap](https://getbootstrap.com/) – CSS framework for styling the admin settings page.
- [Google Gemini API](https://ai.google.dev/) – Integrates AI capabilities for advanced NLP features and function calling. 

## Installation ⚙️

You can install the **Customer Support Bot** plugin in two ways:

### 1. Download from GitHub

- Go to the [GitHub repository](https://github.com/daley-mottley/customer-support-bot) and download the repository as a ZIP file (e.g., from the "Code" button -> "Download ZIP").
- Navigate to your WordPress admin dashboard.
- Go to **Plugins > Add New > Upload Plugin**.
- Choose the downloaded ZIP file and click **Install Now**.
- After installation, click **Activate Plugin**.

### 2. Clone the Repository

Alternatively, you can clone the repository using Git if you have command-line access to your server:

1. Navigate to your WordPress plugin directory (usually located at `wp-content/plugins/`).
2. Clone the repository: `git clone https://github.com/daley-mottley/customer-support-bot.git`
3. Log in to your WordPress admin dashboard.
4. Go to **Plugins > Installed Plugins**.
5. Find **Customer Support Bot** in the list and click **Activate**.

## Configuration ⚙️ 

After activating the plugin, navigate to **Settings -> Chat Widget** in your WordPress admin dashboard. You **must** configure the following for the bot to work:

-   **Gemini API Key:** Enter your API key obtained from [Google AI Studio](https://aistudio.google.com/app/apikey). This is required.
-   **Assistant Name:** Customize the name displayed in the chat header (e.g., "Support Bot", "Booking Assistant").
-   **Avatar Image:** Upload a custom avatar image for the bot.
-   **Bot Greeting:** Set the initial message the bot displays when opened by a user.
-   **Theme & Text Colors:** Customize the primary background color and text color of the chat widget.
-   **Webhook URLs:** If you want to use appointment checking or booking, enter the full webhook URLs provided by your automation service (like Make.com or Zapier) for the "Check Availability" and "Book Appointment" functions.

**Save your settings** after making changes.

## Help Wanted 🪧

We are looking for contributors to help add more features! Some ideas include:

- [ ] **Conversation History:** Implement frontend and backend logic to remember the context of the current chat session.
- [ ] **Knowledge Base Integration:** Allow the bot to search WordPress posts/pages or a custom knowledge base to answer questions.
- [ ] **Basic Troubleshooting**: Provide users with automated solutions for common problems based on predefined flows.
- [ ] **Ticket Categorization**: Automatically sort incoming support tickets (if integrated with a helpdesk).
- [ ] **Status Updates**: Notify users of ticket status and resolution progress (if integrated).
- [ ] **Password Resets**: Allow users to initiate password resets through the bot (requires integration).
- [ ] **Billing Inquiries**: Help users resolve common billing-related questions.
- [ ] **Product Information**: Provide detailed information on products/services.
- [ ] **Return/Refund Processing**: Guide users through the process of returns or refunds (requires integration).
- [ ] **Escalation Management**: Option to escalate unresolved issues to human support agents (e.g., via email or helpdesk ticket).
- [ ] **Satisfaction Surveys**: Gather feedback from users about their support experience.
- [ ] **Multi-language Support**: Offer support in multiple languages.

## Report A Bug 🪰

If you encounter any issues or have questions, feel free to [open an issue](https://github.com/daley-mottley/customer-support-bot/issues/new?assignees=&labels=bug&projects=&template=bug_report.yml&title=%5BBug%5D+) or reach out to the maintainers. Please provide as much detail as possible, including steps to reproduce the bug.

<a name="contributions"></a>
## Contributions 🧑‍🔧👷‍♀️🏗️🏢

Contributions are welcome! It only takes five (5) steps!

To contribute:

1) Fork the repository.
2) Create a new branch for your feature or bug fix: `git checkout -b my-feature-branch`.
3) Make your changes and commit them with clear messages: `git commit -m 'Add some feature'`.
4) Push your changes to your forked repository: `git push origin my-feature-branch`.
5) Open a pull request against the `main` branch of the original repository.

<p align="center" ><strong><em>Please read our <a href="https://github.com/daley-mottley/customer-support-bot/blob/main/CONTRIBUTING.md" >Contributing Guidelines</a> to get started!</em></strong> 🚀</p>

<a name="contributors"></a>
<h2 align="center">Say 'Hi' To Our Contributors!</h2>

<p align="center">
  <a href="https://github.com/daley-mottley">
    <img src="https://github.com/daley-mottley.png" width="100" height="100" style="border-radius: 50%;" alt="daley-mottley"/>
  </a>
  <a href="https://github.com/mostafahanafi">
    <img src="https://github.com/mostafahanafi.png" width="100" height="100" style="border-radius: 50%;" alt="mostafahanafi"/>
  </a>
   <a href="https://github.com/AmanWebDev2">
    <img src="https://github.com/AmanWebDev2.png" width="100" height="100" style="border-radius: 50%;" alt="AmanWebDev2"/>
  </a>
  <a href="https://github.com/kliu57">
    <img src="https://github.com/kliu57.png" width="100" height="100" style="border-radius: 50%;" alt="kliu57"/>
  </a>
  <a href="https://github.com/Shivakarthikeya23">
    <img src="https://github.com/Shivakarthikeya23.png" width="100" height="100" style="border-radius: 50%;" alt="Shivakarthikeya23"/>
  </a>


</p>

<p align="center">🫶 <em>Thank you for your support! </em>🙌 </p>

<hr>

<a name="lets-stay-connected"></a>
<h2 align="center"> 🌎 Let's Stay Connected!🫸🫷 </h2>

<p align="center"> If you like this project and would like to see more features or show your support.</p>
<p align="center"> Feel free to reach out to the developers and give this project a ⭐!</p>
