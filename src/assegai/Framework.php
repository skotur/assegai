<?php

/**
 * This file is part of Assegai
 *
 * Copyright (c) 2013 Guillaume Pasquet
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace assegai
{
    class Framework
    {
        protected $injector;

        function __construct()
        {
            $this->injector = new Injector();

            //// Setting core dependencies.
            // Core.
            $this->injector->register('engine', 'assegai\\AppEngine', array('server', 'mc', 'security', 'router'));
            $this->injector->register('router', 'assegai\\routing\\DefaultRouter');
            $this->injector->register('server', 'assegai\\Server');
            // Request
            $this->injector->register('request', 'assegai\\Request', array('server', 'security'));
            $this->injector->register('mc', 'assegai\\ModuleContainer', array('server'));
            $this->injector->register('response', 'assegai\\Response');
            $this->injector->register('security', 'assegai\\Security');
        }

        function run($conf_path = '')
        {
            $engine = $this->injector->give('engine');
            $engine->setConfiguration($conf_path);
            $request = $this->injector->give('request');
            $request->fromGlobals();
            $engine->serve($request);
        }
    }
}
