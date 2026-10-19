// The landing page's motion and interactions. Each module looks for its own
// markup and does nothing when the page doesn't have it.
import { countUp } from './motion/count-up';
import { heroGlow } from './motion/hero-glow';
import { magnetic } from './motion/magnetic';
import { reveal } from './motion/reveal';
import { caseStudies } from './site/case-studies';
import { contactForm } from './site/contact-form';
import { header } from './site/header';
import { ticker } from './site/ticker';

header();
heroGlow();
ticker();
caseStudies();
countUp();
contactForm();
reveal();
magnetic();
