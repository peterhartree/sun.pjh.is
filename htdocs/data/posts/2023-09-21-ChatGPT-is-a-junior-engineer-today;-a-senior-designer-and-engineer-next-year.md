Date: 2023-09-21
Tags: writing, ai, software development

# ChatGPT is a junior engineer today; a senior designer and engineer next year

If you use several Google accounts, Google Meet does not automatically switch to whatever account has permission to join the call. It's annoying.

Today I hired a team and made a browser extension to solve this. 

It took us less than 30 minutes to get a working prototype. It took a further 3-4 hours to make a "production-ready" version for release on the Chrome Web Store. 

I served as UX designer, engineering manager and head of QA. GPT-4 was our lead developer, and wrote roughly all of the code, to a higher standard than I would have [^1]. 

- [Conversation transcript](https://chat.openai.com/share/20769753-9e5b-4403-a1bb-9336ec88c466)
- [Extension demo video & download](https://chrome.google.com/webstore/detail/join-now-for-google-meet/mljmbebllfeknfjemhnfdmeaadeiclmk)

## How will GPT-5 help us make faster progress?
Here's my guess:
(1) The workflow will be: I give instruction, it updates codebase, it checks the result in browser, fixes codebase if necessary, then asks me to review. [^3]
(2) It will require less of my "expert direction" on how to approach some of the engineering tasks.
(3) It will reply as quickly as GPT-3.5.
(4) It will help me create the logo, screenshot and demo video for the Chrome Web Store.
(5) It will help me design the user interface.
(6) It will proactively suggest feature ideas and possible UX issues.

I expect most of these improvements will be available by the end of 2024. With them, I'd be able to create this extension in less than an hour.

Today, my background in software design and engineering was a necessary condition to deliver a great result within a couple of hours (see the transcript). How much of this expertise will be needed in 2024? 2025?

**Prediction:** by December 2025, my wife (who has no software design or engineering experience) will be able to create the same extension in less than a day. 

[^1]: The full codebase is some 500 lines. Less than 5 of these were entirely written by me. Perhaps 30-50 were started by me, and completed by our intern (Github Copilot).
[^2]: I tried using the GPT-4 Canva plugin but it was totally broken.
[^3]: Open Interpreter (released last week) now offers this workflow for writing blocks of code, but until these systems can "see", I'll need to do a bunch of the software testing in browser. Multimodal LLMs are due before the end of this year.