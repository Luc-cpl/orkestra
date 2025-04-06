import React from 'react';
import Layout from '@theme/Layout';
import CodeBlock from '@theme/CodeBlock';
import useDocusaurusContext from '@docusaurus/useDocusaurusContext';
import Link from '@docusaurus/Link';
import { motion } from 'framer-motion';
import { ArrowRightIcon, CodeBracketIcon, CubeIcon, RocketLaunchIcon, ServerIcon, ShieldCheckIcon } from '@heroicons/react/24/outline';

export default function Home() {
  const { siteConfig } = useDocusaurusContext();

  // Animation variants
  const fadeIn = {
    hidden: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0, transition: { duration: 0.6 } }
  };

  const staggerContainer = {
    hidden: { opacity: 0 },
    visible: {
      opacity: 1,
      transition: {
        staggerChildren: 0.2
      }
    }
  };

  return (
    <Layout
      title={`${siteConfig.title} - A Progressive PHP Framework`}
      description="Orkestra is a future-ready PHP framework designed for speed, flexibility, and scalability. Build modern server-side applications with confidence."
    >
      {/* Hero Section */}
      <header className="relative overflow-hidden bg-gradient-to-br from-primary to-primary-dark text-white">
        <div className="absolute inset-0 bg-grid-white/[0.05] bg-[size:60px_60px]"></div>
        <div className="absolute inset-0 bg-gradient-to-t from-primary-dark/80 to-transparent"></div>
        
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
          <motion.div 
            className="flex flex-col md:flex-row items-center gap-16"
            initial="hidden"
            animate="visible"
            variants={staggerContainer}
          >
            <motion.div className="md:w-1/2 text-center md:text-left space-y-8" variants={fadeIn}>
              <motion.h1 
                className="text-5xl lg:text-6xl font-extrabold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-accent-light"
                variants={fadeIn}
              >
                Build the Future with Orkestra
              </motion.h1>
              <motion.p 
                className="text-xl md:text-2xl text-white/90 max-w-2xl"
                variants={fadeIn}
              >
                A modern PHP framework for building reliable, efficient, and scalable server-side applications — fast.
              </motion.p>
              <motion.div 
                className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start pt-4"
                variants={fadeIn}
              >
                <Link
                  to="/docs/intro"
                  className="group px-8 py-3 text-base font-medium rounded-lg text-primary bg-white hover:bg-accent-light hover:text-white! hover:no-underline! transition-all duration-300 flex items-center justify-center"
                >
                  Read the Docs
                  <ArrowRightIcon className="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" />
                </Link>
                <Link
                  to="https://github.com/Luc-cpl/orkestra"
                  className="px-8 py-3 text-base font-medium rounded-lg text-white! bg-accent hover:bg-accent-dark hover:no-underline! transition-colors duration-300"
                >
                  View on GitHub
                </Link>
              </motion.div>
            </motion.div>
            
            <motion.div 
              className="md:w-1/2"
              variants={fadeIn}
              whileHover={{ scale: 1.02 }}
              transition={{ type: "spring", stiffness: 300 }}
            >
              <div className="bg-primary-darker/50 backdrop-blur-sm rounded-xl overflow-hidden shadow-2xl">
                <CodeBlock language="bash" className="!m-0">
{`# Create a new project
composer create-project luccpl/orkestra-skeleton my_project
cd my_project

# Start the development server
php maestro app:serve`}
                </CodeBlock>
              </div>
            </motion.div>
          </motion.div>
        </div>
      </header>

      {/* Core Features */}
      <section className="py-24 bg-light dark:bg-slate-900">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div 
            className="text-center max-w-3xl mx-auto mb-20"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
          >
            <h2 className="text-4xl font-bold text-primary dark:text-white mb-6">
              A Framework That Works with You
            </h2>
            <p className="text-lg text-slate-600 dark:text-slate-300">
              Orkestra empowers developers to build expressive, scalable applications with minimal overhead.
            </p>
          </motion.div>

          <motion.div 
            className="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12"
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            variants={staggerContainer}
          >
            {[
              {
                title: 'Extensible',
                desc: 'Highly modular by nature — tailor your architecture and tooling to your project\'s needs.',
                icon: <CubeIcon className="h-8 w-8 text-accent" />
              },
              {
                title: 'Versatile',
                desc: 'From APIs to full apps, Orkestra gives you the structure without the rigidity.',
                icon: <ServerIcon className="h-8 w-8 text-accent" />
              },
              {
                title: 'Progressive',
                desc: 'Built on modern PHP best practices like attributes, typed APIs, and dependency injection.',
                icon: <RocketLaunchIcon className="h-8 w-8 text-accent" />
              },
            ].map((item, i) => (
              <motion.div
                key={i}
                className="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border border-slate-100 dark:border-slate-700 flex flex-col items-center text-center"
                variants={fadeIn}
                whileHover={{ y: -5 }}
              >
                <div className="mb-6 p-3 bg-light dark:bg-slate-700 rounded-lg">{item.icon}</div>
                <h3 className="text-xl font-semibold text-primary dark:text-white mb-4">{item.title}</h3>
                <p className="text-slate-600 dark:text-slate-300">{item.desc}</p>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Extended Features */}
      <section className="py-24 bg-white dark:bg-slate-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div 
            className="text-center max-w-3xl mx-auto mb-20"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
          >
            <h2 className="text-4xl font-bold text-primary dark:text-white mb-6">Built-In Power</h2>
            <p className="text-lg text-slate-600 dark:text-slate-300">
              Everything you need to go from idea to production.
            </p>
          </motion.div>

          <motion.div 
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            variants={staggerContainer}
          >
            {[
              {
                title: 'Modularity',
                desc: 'Compose apps with isolated, reusable modules for better maintainability.',
                icon: <CodeBracketIcon className="h-6 w-6 text-accent" />
              },
              {
                title: 'Scalability',
                desc: 'Scale confidently with performance-first architecture and optimized internals.',
                icon: <RocketLaunchIcon className="h-6 w-6 text-accent" />
              },
              {
                title: 'Dependency Injection',
                desc: 'Inject and mock services easily using a clean, service-oriented approach.',
                icon: <ShieldCheckIcon className="h-6 w-6 text-accent" />
              },
            ].map((item, i) => (
              <motion.div 
                key={i} 
                className="bg-light dark:bg-slate-900 p-8 rounded-xl shadow-md hover:shadow-lg transition-all duration-300"
                variants={fadeIn}
                whileHover={{ y: -5 }}
              >
                <div className="flex items-center mb-4">
                  <div className="mr-4 p-2 bg-white dark:bg-slate-800 rounded-lg">{item.icon}</div>
                  <h3 className="text-lg font-semibold text-primary dark:text-white">{item.title}</h3>
                </div>
                <p className="text-slate-600 dark:text-slate-300">{item.desc}</p>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Sample Code */}
      <section className="py-24 bg-light dark:bg-slate-900">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <motion.div 
            className="text-center max-w-3xl mx-auto mb-20"
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.6 }}
          >
            <h2 className="text-4xl font-bold text-primary dark:text-white mb-6">Code That Makes Sense</h2>
            <p className="text-lg text-slate-600 dark:text-slate-300">
              Write clean, powerful controllers using modern PHP features and Orkestra's expressive syntax.
            </p>
          </motion.div>

          <motion.div 
            className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start"
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            variants={staggerContainer}
          >
            <motion.div 
              className="rounded-xl overflow-hidden shadow-lg"
              variants={fadeIn}
              whileHover={{ scale: 1.02 }}
              transition={{ type: "spring", stiffness: 300 }}
            >
              <CodeBlock language="php" className="!m-0">
{`<?php

namespace App\\Http\\Controllers;

use Orkestra\\Services\\Http\\AbstractController;
use Orkestra\\Services\\Http\\Attributes\\Entity;
use Psr\\Http\\Message\\ServerRequestInterface;
use App\\Entities\\Post;

class CreatePostController extends AbstractController
{
    #[Entity(Post::class)]
    public function __invoke(ServerRequestInterface $request): Post
    {
        $body = $request->getParsedBody();
        return $this->entityFactory->create(Post::class, ...$body);
    }
}`}
              </CodeBlock>
            </motion.div>

            <motion.div 
              className="space-y-8"
              variants={fadeIn}
            >
              {[
                {
                  title: 'PSR Compliance',
                  desc: 'Orkestra is fully compliant with PSR interfaces, ensuring seamless integration with any PHP application.',
                },
                {
                  title: 'Robust Validation',
                  desc: 'Validation logic is embedded where it belongs, next to the parameter it applies to.',
                },
                {
                  title: 'Type Safety',
                  desc: 'Strong typing, enum support, and automatic conversion give you predictable code.',
                },
              ].map((f, i) => (
                <motion.div 
                  key={i}
                  className="p-6 bg-white dark:bg-slate-800 rounded-xl shadow-md"
                  whileHover={{ x: 5 }}
                  transition={{ type: "spring", stiffness: 400 }}
                >
                  <h3 className="text-xl font-bold text-primary dark:text-white mb-3">{f.title}</h3>
                  <p className="text-slate-600 dark:text-slate-300">{f.desc}</p>
                </motion.div>
              ))}
            </motion.div>
          </motion.div>
        </div>
      </section>
    </Layout>
  );
}
