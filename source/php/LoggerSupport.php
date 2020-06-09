<?php

namespace EventManagerIntegration;

trait LoggerSupport {
  private function log($msg) {
    error_log("INFO [" . get_called_class() . "]: $msg");
  }

  private function error($msg) {
    error_log("ERROR [" . get_called_class() . "]: $msg");
  }
}
