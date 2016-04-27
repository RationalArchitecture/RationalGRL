# Introduction
Goal modeling languages, such as i* and the Goal-oriented Requirements Language (GRL), capture and analyze high-level goals and their relationships with lower level goals and tasks. However, in such models, the rationalization behind these goals and tasks and the selection of alternatives are usually left implicit.Rationalization consists of arguments for and against certain goals and solutions, which allow checking whether a particular goal model is a correct rendering of the relevant stakeholders' opinions and discussions. To better integrate goal models and their rationalization, we develop the RationalGRL framework, in which argument diagrams can be mapped to goal models. Moreover, we integrate the result of the evaluation of arguments and their counterarguments with GRL initial satisfaction values. We develop an interface between the argument web tools OVA and TOAST and the Eclipse-based tool for GRL called jUCMNav. 

# Overview of the Framework
![Overview of the Framework](overview.png)

# Project Content

* `Case study description`: A description of the case study eGovernment that we used.
* `GRL Extension`: A set of OCL rules that can be imported into the jUCMNav Eclipse-based tool. 
The folder also contains installation instructions, GRL models and screenshots of these models.
