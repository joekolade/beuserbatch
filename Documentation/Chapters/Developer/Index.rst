.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer:

Developer Corner
================

The extension works with models for user that should be importet.

Wehn sending in a file to the ``checkAction`` the corresponding table ``tx_beuserbatch_domain_model_importuser`` and sets up the found users in the csv file.

Users that don't have an usergoup id in the csv will be **admins**.
