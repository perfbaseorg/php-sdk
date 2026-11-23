<?php

// Namespaced overrides for the extension functions PerfbaseExtension calls
// unqualified. PHP resolves these before falling back to the global
// functions, so they simulate the extension returning NULL (no data /
// allocator failure) whether or not ext-perfbase is loaded. Only include
// this from a test that runs in a separate process.

namespace Perfbase\SDK\Extension;

function perfbase_get_data()
{
    return null;
}

function perfbase_version()
{
    return null;
}
