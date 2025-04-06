"use strict";(self.webpackChunkwebsite=self.webpackChunkwebsite||[]).push([[924],{4575:(e,n,t)=>{t.r(n),t.d(n,{assets:()=>a,contentTitle:()=>l,default:()=>u,frontMatter:()=>i,metadata:()=>r,toc:()=>c});const r=JSON.parse('{"id":"getting-started/installation","title":"Installation","description":"Prerequisites","source":"@site/docs/getting-started/installation.md","sourceDirName":"getting-started","slug":"/getting-started/installation","permalink":"/docs/getting-started/installation","draft":false,"unlisted":false,"editUrl":"https://github.com/Luc-cpl/orkestra/tree/main/docs/docs/getting-started/installation.md","tags":[],"version":"current","sidebarPosition":1,"frontMatter":{"sidebar_position":1},"sidebar":"tutorialSidebar","previous":{"title":"Introduction","permalink":"/docs/intro"},"next":{"title":"Configuration","permalink":"/docs/getting-started/configuration"}}');var s=t(4848),o=t(8453);const i={sidebar_position:1},l="Installation",a={},c=[{value:"Prerequisites",id:"prerequisites",level:2},{value:"Using the Skeleton",id:"using-the-skeleton",level:2},{value:"Project Structure",id:"project-structure",level:2},{value:"Starting the Development Server",id:"starting-the-development-server",level:2},{value:"Module Structure",id:"module-structure",level:2},{value:"Next Steps",id:"next-steps",level:2}];function d(e){const n={a:"a",code:"code",h1:"h1",h2:"h2",header:"header",li:"li",p:"p",pre:"pre",strong:"strong",ul:"ul",...(0,o.R)(),...e.components};return(0,s.jsxs)(s.Fragment,{children:[(0,s.jsx)(n.header,{children:(0,s.jsx)(n.h1,{id:"installation",children:"Installation"})}),"\n",(0,s.jsx)(n.h2,{id:"prerequisites",children:"Prerequisites"}),"\n",(0,s.jsx)(n.p,{children:"Before you begin, ensure you have the following prerequisites installed:"}),"\n",(0,s.jsxs)(n.ul,{children:["\n",(0,s.jsx)(n.li,{children:"PHP 8.2 or higher"}),"\n",(0,s.jsx)(n.li,{children:"Composer"}),"\n"]}),"\n",(0,s.jsx)(n.h2,{id:"using-the-skeleton",children:"Using the Skeleton"}),"\n",(0,s.jsx)(n.p,{children:"The easiest way to get started with Orkestra is to use the official skeleton project. This will create a new project with all the necessary files and configurations."}),"\n",(0,s.jsx)(n.pre,{children:(0,s.jsx)(n.code,{className:"language-bash",children:"composer create-project luccpl/orkestra-skeleton {project_name}\ncd {project_name}\n"})}),"\n",(0,s.jsx)(n.h2,{id:"project-structure",children:"Project Structure"}),"\n",(0,s.jsx)(n.p,{children:"After installation, your project will have the following structure:"}),"\n",(0,s.jsx)(n.pre,{children:(0,s.jsx)(n.code,{children:"{project_name}/\n\u251c\u2500\u2500 app/\n\u2502   \u251c\u2500\u2500 Controllers/\n\u2502   \u251c\u2500\u2500 Views/\n\u2502   \u2514\u2500\u2500 Providers/\n\u251c\u2500\u2500 config/\n\u2502   \u251c\u2500\u2500 app.php\n\u2502   \u2514\u2500\u2500 routes.php\n\u251c\u2500\u2500 public/\n\u251c\u2500\u2500 storage/\n\u251c\u2500\u2500 vendor/\n\u251c\u2500\u2500 composer.json\n\u2514\u2500\u2500 maestro\n"})}),"\n",(0,s.jsx)(n.h2,{id:"starting-the-development-server",children:"Starting the Development Server"}),"\n",(0,s.jsx)(n.p,{children:"Once you have created your project, you can start the development server using the following command:"}),"\n",(0,s.jsx)(n.pre,{children:(0,s.jsx)(n.code,{className:"language-bash",children:"php maestro app:serve\n"})}),"\n",(0,s.jsxs)(n.p,{children:["This will start a development server, typically at ",(0,s.jsx)(n.code,{children:"http://localhost:8000"}),". You can access your application by opening this URL in your web browser."]}),"\n",(0,s.jsx)(n.h2,{id:"module-structure",children:"Module Structure"}),"\n",(0,s.jsxs)(n.p,{children:[(0,s.jsx)(n.strong,{children:"For large projects"}),", we recommend to follow a modular structure, avoiding nesting different services in your project, for this you can change your composer.json file to autoload packages from a ",(0,s.jsx)(n.code,{children:"./modules"})," directory and then encapsulate each part of your application in a separated module:"]}),"\n",(0,s.jsx)(n.pre,{children:(0,s.jsx)(n.code,{className:"language-json",children:'// composer.json\n{\n    "autoload": {\n        "psr-4": {\n            "Modules\\\\": "modules/"\n        }\n    }\n}\n'})}),"\n",(0,s.jsx)(n.pre,{children:(0,s.jsx)(n.code,{children:"\u251c\u2500\u2500 modules/\n\u2502   \u251c\u2500\u2500 Auth/\n|   |   \u251c\u2500\u2500 Commands/\n\u2502   \u2502   \u251c\u2500\u2500 Controllers/\n\u2502   \u2502   \u251c\u2500\u2500 Actions/\n\u2502   \u2502   \u251c\u2500\u2500 AuthProvider.php\n\u2502   \u2514\u2500\u2500 Subscriptions/\n\u2502   \u2502   \u251c\u2500\u2500 Controllers/\n\u2502   \u2502   \u251c\u2500\u2500 Repositories/\n\u2502   \u2502   \u251c\u2500\u2500 Services/\n\u2502   \u2502   \u251c\u2500\u2500 SubscriptionsProvider.php\n"})}),"\n",(0,s.jsx)(n.h2,{id:"next-steps",children:"Next Steps"}),"\n",(0,s.jsxs)(n.ul,{children:["\n",(0,s.jsxs)(n.li,{children:[(0,s.jsx)(n.a,{href:"/docs/getting-started/configuration",children:"Configuration"})," - Learn how to configure your Orkestra application"]}),"\n",(0,s.jsxs)(n.li,{children:[(0,s.jsx)(n.a,{href:"/docs/guides/routing",children:"Routing"})," - Define routes for your application"]}),"\n",(0,s.jsxs)(n.li,{children:[(0,s.jsx)(n.a,{href:"/docs/guides/controllers",children:"Controllers"})," - Create controllers for your application"]}),"\n",(0,s.jsxs)(n.li,{children:[(0,s.jsx)(n.a,{href:"/docs/guides/providers",children:"Service Providers"})," - Register services for your application"]}),"\n"]})]})}function u(e={}){const{wrapper:n}={...(0,o.R)(),...e.components};return n?(0,s.jsx)(n,{...e,children:(0,s.jsx)(d,{...e})}):d(e)}},8453:(e,n,t)=>{t.d(n,{R:()=>i,x:()=>l});var r=t(6540);const s={},o=r.createContext(s);function i(e){const n=r.useContext(o);return r.useMemo((function(){return"function"==typeof e?e(n):{...n,...e}}),[n,e])}function l(e){let n;return n=e.disableParentContext?"function"==typeof e.components?e.components(s):e.components||s:i(e.components),r.createElement(o.Provider,{value:n},e.children)}}}]);