<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

/**
 * If device detection is in use and was evaluated for the current request
 * a string identifying the "view" that is to be delivered to the user will
 * be pushed into an environment variable by the server.
 *
 * This string is completely up to the app developer as what view is given
 * to the user is not neecessarily going to match exactly to the form
 * factor of the device.  For example, a smarttv might be shown the
 * "desktop" view of the app.
 *
 * Examples: desktop, smartphone, smarttv, etc.
 *
 * @link https://www.scientiamobile.com/wurflCapability
 *
 * This class holds an array of enabled views and a default.  Resolving
 * a value (where it comes from is not relevant) will return the same
 * value back (indicating it was valid and okay to use) or the default
 * device view which should be used as is.
 *
 */
final class DeviceViewResolver
{
    private $enabledViews = [];
    private $default;

    /**
     * @param string $default
     * @param array  $enabledViews
     */
    public function __construct(string $default = 'desktop', array $enabledViews = [])
    {
        foreach ($enabledViews as $view) {
            $view = $this->sanitize($view);
            if (!empty($view)) {
                $this->enabledViews[$view] = true;
            }
        }

        $default = $this->sanitize($default);
        $this->default = $this->isValid($default) ? $default : key($this->enabledViews);
    }

    /**
     * @param string $view
     *
     * @return string
     */
    public function resolve(?string $view = null): string
    {
        $view = $this->sanitize("{$view}");
        if ($this->isValid($view)) {
            return $view;
        }

        return $this->default;
    }

    /**
     * @param string $view
     *
     * @return string
     */
    private function sanitize(string $view): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $view));
    }

    /**
     * @param string $view
     *
     * @return bool
     */
    private function isValid(string $view): bool
    {
        return !empty($view) && isset($this->enabledViews[$view]);
    }
}
